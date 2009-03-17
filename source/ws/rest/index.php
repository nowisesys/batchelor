<?php

// -------------------------------------------------------------------------------
//  Copyright (C) 2007-2008 Anders L�vgren
//
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation; either version 2 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
// -------------------------------------------------------------------------------

// 
// This is an implementation of the REST web service architecture for 
// Batchelor based on information from these document:
// 
//   * http://www.xfront.com/REST-Web-Services.html
//   * http://www.xml.com/pub/a/2004/08/11/rest.html
//
// The HTTP status code is always 200. The response is always wrapped inside
// result tags, where the state attribute is either "success" or "failed".
// The content (the embedded message) is either an list of links or an object,
// where the object is the requested data, an status message or an error
// object. 
// 
// An example error message (missing method) looks like this:
// 
//   <result state="failed" type="error">
//     <error>
//       <code>3</code>
//       <message>No such method</message>
//     </error>
//   </result>
// 
// The REST web services is presented as a tree of URI's. Each URI has one
// or more associated HTTP action(s) (standard GET, POST, PUT or DELETE).
// All GET requests are non-modifying. An URI (a node resource) can be
// changed by using PUT or POST (add or modified) or DELETE (removed).
// 
// The output format from a GET request on an URI is either an list (of 
// links) or data (possibly multiple objects). The format is selected in
// two ways: either append format={list|data} to the request URI or append
// the format to the URI path.
// 
// Example:
// 
//   /queue/all?format=data   // get all jobs
//   /queue/all/data          // alternative way
// 
// An modifying HTTP action (PUT, POST, DELETE) will return a status message.
// Heres an example response for dequeue (removing) a job:
// 
//   <result state="success" type="status">
//     <status>Removed job 1355</status>
//   </result>
//
// An link has possible action attributes like get, put, post and delete. The
// action attribute value describes the object returned by taking this action.
// 
// Example:
// 
//   <result state="success" type="link">
//     <link xlink:href="/queue" get="link" put="job" />
//     <link xlink:href="/result" get="link" />
//        ...
//   </result>
// 
// The XML above tells us that the /queue URI supports GET and PUT, whereas
// the /result URI only accepts GET.
// 
// The web service utility (utils/ws.php) can be used to browse the REST
// service. Start with: 'php ws.php --type=rest --params='' and then append
// the relative URI path in the params option.
// 
// Heres an schematic overview of the tree with its nodes. Accepted actions
// for each node is listed on the right.
// 
// Node:                      Action:       Description:
// ------                     --------      -------------
// 
// root/                      GET           (the ws/rest service root)
//   +-- queue/               GET,PUT,POST  (get sort and filter, enqueue with PUT)
//   |      +-- all/          GET,DELETE    (get or delete all objects)
//   |            +-- xxx/    GET,DELETE    (get or delete single job)
//   |      +-- sort/         GET
//   |            +-- xxx/    GET           (various sort options)
//   |      +-- filter/       GET
//   |            +-- xxx/    GET,DELETE    (get or delete all jobs matching filter)
//   +-- result/              GET           (list all job directories)
//   |      +-- dir/          GET           (list result files)
//   |            +-- <file>  GET           (get content of result file)
//   +-- watch/               POST          (watch jobs)
//   |      +-- <job>         GET           (get info about job)
//   +-- errors/              GET           (list all error types)
//   |      +-- <error>       GET           (get error object)
//   +-- suspend/             GET           (list suspendable jobs)
//   |      +-- <job>         GET,POST      (get info or suspend the job)
//   +-- resume/              GET           (list resumable jobs)
//   |      +-- <job>         GET,POST      (get info or resume the job)
// 
// 
// The nodes errors, suspend, resume, queue, result and watch is set as
// the 'method' member in the request object. The node path is available
// in the 'path' member. Additional child nodes are available in the
// 'childs' member (an array).
// 

ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . "../../../");

// 
// Define the XML-schema URL:
// 
define("WS_REST_SCHEMA_URL", "http://it.bmc.uu.se/batchelor/rest/200801");

//
// Get configuration.
// 
include "conf/config.inc";

include "include/common.inc";
include "include/queue.inc";
include "include/ws.inc";
include "include/delete.inc";

// 
// Output is always XML:
// 
header("Content-Type: text/xml");
header("Connection: close");

// 
// Initilize the REST session.
// 
ws_rest_session_setup();

// 
// The error handler for the REST web service. The error parameter gets send
// to the client (peer). If any error are set by put_error(), then they are
// flushed to the web server log.
// 
function send_error($code, $message, $headers = false, $loop = false) 
{ 
    log_errors(true);
    if($headers) {
	printf("<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n");
    }
    if(!$loop) {
	send_start_tag("failed", "error");
    }
    printf("  <tns:error>\n");
    printf("    <tns:code>%d</tns:code>\n", $code);
    printf("    <tns:message>%s</tns:message>\n", isset($message) ? $message : get_error($code));
    printf("  </tns:error>\n");
    if(!$loop) {
	send_end_tag();
	exit(1);
    }
}

// 
// Decode request. We need to decode the request from the URI.
// 
function decode_request()
{
    $request = array();
    while(strstr($_SERVER['REQUEST_URI'], "//")) {
	$_SERVER['REQUEST_URI'] = str_replace("//", "/", $_SERVER['REQUEST_URI']);
    }
    $_SERVER['REQUEST_URI'] = trim($_SERVER['REQUEST_URI'], "/");

    if(strstr($_SERVER['REQUEST_URI'], "?")) {
	list($path, $params) = explode("?", $_SERVER['REQUEST_URI']);
	foreach(explode("&", $params) as $param) {
	    list($key, $val) = explode("=", $param);
	    if(!isset($val)) {
		$val = $key;
	    }
	    $request[$key] = $val;
	}
    } else {
	$path = $_SERVER['REQUEST_URI'];
    }
    $parts = explode("/", $path);
    foreach($parts as $part) {
	if(strlen($part) == 0) {
	    array_shift($parts);
	}
    }
    for($pos = 0; $pos < count($parts); $pos++) {
	if($parts[$pos] == "rest") {
	    $pos++;
	    break;
	}
    }        
    $request['base'] = sprintf("http://%s", $_SERVER['SERVER_NAME']);
    for($i = 0; $i < $pos; $i++) {
	$request['base'] .= "/" . array_shift($parts);
    }
    $request['path'] = "/" . implode("/", $parts);
    $request['method'] = array_shift($parts);
    if(count($parts)) {
	$request['childs'] = $parts;
    }
    return (object)$request;
}

// 
// Send header.
// 
function send_start_tag($state, $type) 
{
    printf("<tns:result state=\"%s\" type=\"%s\"\n   xmlns:tns=\"%s\"\n   xmlns:xlink=\"http://http.w3.org/1999/xlink\">\n", 
	   $state, $type, WS_REST_SCHEMA_URL);
}

// 
// Send footer.
// 
function send_end_tag() 
{
    print "</tns:result>\n";
}

// 
// Send resource link. The attr argument contains an array of supported
// actions for this link, and the expected object returned by taking this
// action (like get => object). If attr is a string, then its interpret
// as the get action.
// 
function send_link($href, $attr = null)
{
    $actions = "";
    if(isset($attr)) {
	if(is_array($attr)) {
	    foreach($attr as $action => $object) {
		$actions .= "$action=\"$object\" ";
	    }
	} elseif(is_string($attr)) {
	    $actions = "get=\"$attr\"";
	}
    }
    printf("  <tns:link xlink:href=\"%s\" $actions />\n", $href);
}

// 
// Send status message in reponse to an modifying action.
// 
function send_status($message)
{
    printf("  <tns:status>$message</tns:status>\n");
}

// 
// Send root.
// 
function send_root($request)
{
    send_start_tag("success", "link");
    send_link(sprintf("%s/queue", $request->base), array( "get" => "link", "put" => "job"));
    send_link(sprintf("%s/result", $request->base), "link");
    send_link(sprintf("%s/watch", $request->base), "link");
    send_link(sprintf("%s/errors", $request->base), "link");
    // 
    // Don't expose suspend and resume methods unless job control is enabled.
    // 
    if(defined("ENABLE_JOB_CONTROL") && ENABLE_JOB_CONTROL != "off") {
	send_link(sprintf("%s/suspend", $request->base), "link");
	send_link(sprintf("%s/resume", $request->base), "link");
    }
    send_end_tag();
}

// 
// Send all errors or specific error message.
// 
function send_errors($request) 
{
    // 
    // Only request method GET is accepted.
    // 
    if($_SERVER['REQUEST_METHOD'] != "GET") {
	send_error(WS_ERROR_REQUEST_METHOD, null);
    }
    
    if(isset($request->childs) || isset($request->format)) {
	if((isset($request->childs) && $request->childs[0] == "list") || 
	   (isset($request->format) && $request->format == "list")) {
	    $errors = get_error();
	    send_start_tag("success", "link");
	    for($i = 0; $i < count($errors); $i++) {
		send_link(sprintf("%s/%s/%d", 
				  $request->base, $request->method, $i + 1),
			  "error");
	    }
	    send_end_tag();
	} elseif((isset($request->childs) && $request->childs[0] == "data") ||
		 (isset($request->format) && $request->format == "data")) {
	    $errors = get_error();
	    send_start_tag("success", "error");
	    for($i = 0; $i < count($errors); $i++) {
		send_error($i + 1, $errors[$i], false, true);
	    }
	    send_end_tag();
	} else {
	    $error = get_error($request->childs[0]);
	    if(isset($error)) {
		send_start_tag("success", "error");
		send_error($request->childs[0], $error, false, true);
		send_end_tag();
	    } else {
		send_error(WS_ERROR_INVALID_REQUEST, null);
	    }
	}
    } else {
	send_start_tag("success", "link");
	send_link(sprintf("%s/errors/list", $request->base), "link");
	send_link(sprintf("%s/errors/data", $request->base), "error");
	send_end_tag();
    }
}

// 
// The suspend method.
// 
function send_suspend($request)
{    
    if(!defined("ENABLE_JOB_CONTROL") || ENABLE_JOB_CONTROL == "off") {
	send_error(WS_ERROR_DISALLOWED, "job control is not enabled");
    }
    if(isset($request->childs)) {
	if($_SERVER['REQUEST_METHOD'] != "GET" && 
	   $_SERVER['REQUEST_METHOD'] != "POST") {
	    send_error(WS_ERROR_REQUEST_METHOD, null);
	}
	if($_SERVER['REQUEST_METHOD'] == "GET") {
	    // 
	    // Return job.
	    // 
	    $jobs = array();
	    if(!ws_queue($jobs)) {
		send_error(WS_ERROR_FAILED_CALL_METHOD, get_last_error());
	    }
	    send_start_tag("success", "job");
	    foreach($jobs as $result => $job) {
		if($request->childs[0] == $result &&
		   $request->childs[1] == $job['jobid']) {
		    $job['result'] = $result;		    
		    send_job($job, $request);
		}
	    }
	    send_end_tag();
	} else {
	    // 
	    // Suspend job.
	    // 
	    if(!ws_suspend($request->childs[0], $request->childs[1])) {
		send_error(WS_ERROR_FAILED_CALL_METHOD, get_last_error());
	    }
	    send_start_tag("success", "link");
	    send_link(sprintf("%s/suspend/%s/%s",
			      $request->base,
			      $request->childs[0],
			      $request->childs[1]),
		      "link");
	    send_end_tag();
	}
    } else {
	if($_SERVER['REQUEST_METHOD'] != "GET") {
	    send_error(WS_ERROR_REQUEST_METHOD, null);
	}
	
	// 
	// Return suspendable jobs.
	// 
	$jobs = array();
	if(!ws_queue($jobs)) {
	    send_error(WS_ERROR_FAILED_CALL_METHOD, get_last_error());
	}
	send_start_tag("success", "link");
	foreach($jobs as $result => $job) {
	    if($job['state'] == "running") {
		$sigfile = sprintf("%s/jobs/%s/%s/signal", CACHE_DIRECTORY, $GLOBALS['hostid'], $result);
		if(file_exists($sigfile)) {
		    $signal = file_get_contents($sigfile);
		    if($signal == "stop") {
			continue;         // already suspended
		    }
		}
		send_link(sprintf("%s/suspend/%s/%s",
				  $request->base,
				  $result,
				  $job['jobid']),
			  "link");
	    }
	}
	send_end_tag();
    }
}

// 
// The resume method.
// 
function send_resume($request)
{
    if(!defined("ENABLE_JOB_CONTROL") || ENABLE_JOB_CONTROL == "off") {
	send_error(WS_ERROR_DISALLOWED, "job control is not enabled");
    }
    if(isset($request->childs)) {
	if($_SERVER['REQUEST_METHOD'] != "GET" && 
	   $_SERVER['REQUEST_METHOD'] != "POST") {
	    send_error(WS_ERROR_REQUEST_METHOD, null);
	}
	if($_SERVER['REQUEST_METHOD'] == "GET") {
	    // 
	    // Return job.
	    // 
	    $jobs = array();
	    if(!ws_queue($jobs)) {
		send_error(WS_ERROR_FAILED_CALL_METHOD, get_last_error());
	    }
	    send_start_tag("success", "job");
	    foreach($jobs as $result => $job) {
		if($request->childs[0] == $result &&
		   $request->childs[1] == $job['jobid']) {
		    $job['result'] = $result;
		    send_job($job, $request);
		}
	    }
	    send_end_tag();
	} else {
	    // 
	    // Resume job.
	    // 
	    if(!ws_resume($request->childs[0], $request->childs[1])) {
		send_error(WS_ERROR_FAILED_CALL_METHOD, get_last_error());
	    }
	    send_start_tag("success", "link");
	    send_link(sprintf("%s/resume/%s/%s",
			      $request->base,
			      $request->childs[0],
			      $request->childs[1]),
		      "link");
	    send_end_tag();
	}
    } else {
	if($_SERVER['REQUEST_METHOD'] != "GET") {
	    send_error(WS_ERROR_REQUEST_METHOD, null);
	}	
	// 
	// Return resumable jobs.
	// 
	$jobs = array();
	if(!ws_queue($jobs)) {
	    send_error(WS_ERROR_FAILED_CALL_METHOD, get_last_error());
	}
	send_start_tag("success", "link");
	foreach($jobs as $result => $job) {
	    if($job['state'] == "running") {
		$sigfile = sprintf("%s/jobs/%s/%s/signal", CACHE_DIRECTORY, $GLOBALS['hostid'], $result);
		if(file_exists($sigfile)) {
		    $signal = file_get_contents($sigfile);
		    if($signal == "stop") {
			send_link(sprintf("%s/resume/%s/%s",
					  $request->base,
					  $result,
					  $job['jobid']),
				  "link");
		    }
		}
	    }
	}
	send_end_tag();
    }
}

// 
// Send a single job object.
// 
function send_job(&$job, $request)
{
    printf("  <tns:job timezone=\"%s\">\n", ini_get("date.timezone"));
    foreach($job as $key => $val) {
	if($key == "name") {
	    printf("    <%s>%s</%s>\n", $key, utf8_encode($val), $key);
	} else {
	    printf("    <%s>%s</%s>\n", $key, $val, $key);
	}
    }
    print "  </tns:job>\n";
}

// 
// Helper function for send_queue().
// 
function send_queue_helper($request, $format, $sort, $filter)
{
    // 
    // Send all jobs either as an list or as an array of job objects.
    // 
    $jobs = array();
    if(!ws_queue($jobs, $sort, $filter)) {
	send_error(WS_ERROR_FAILED_CALL_METHOD, get_last_error());
    }
    if($format == "data") {
	send_start_tag("success", "job");
	foreach($jobs as $result => $job) {
	    $job['result'] = $result;
	    send_job($job, $request);
	}
	send_end_tag();
    } elseif($format == "list") {
	send_start_tag("success", "link");
	foreach($jobs as $result => $job) {
	    send_link(sprintf("%s/queue/%s/%s", 
			      $request->base,
			      $result, $job['jobid']), 
		      array( "get" => "job", "delete" => "status" ));
	}
	send_end_tag();
    }
}

// 
// The queue method.
//
function send_queue($request)
{
    if(isset($request->childs)) {
	switch($request->childs[0]) {
	 case "all":
	    if(isset($request->childs[1])) {
		send_queue_helper($request, $request->childs[1], "none", "all");
	    } elseif(isset($request->format)) {
		send_queue_helper($request, $request->format, "none", "all");
	    } else {
		if($_SERVER['REQUEST_METHOD'] != "GET" && 
		   $_SERVER['REQUEST_METHOD'] != "DELETE") {
		    send_error(WS_ERROR_REQUEST_METHOD, null);
		}
		if($_SERVER['REQUEST_METHOD'] == "DELETE") {
		    $jobs = array();
		    if(!ws_queue($jobs)) {
			send_error(WS_ERROR_FAILED_CALL_METHOD, get_last_error());
		    }
		    foreach($jobs as $result => $job) {
			if(!ws_dequeue($result, $job['jobid'])) {
			    send_error(WS_ERROR_FAILED_CALL_METHOD, get_last_error());
			}
		    }
		    send_start_tag("success", "link");
		    send_status(sprintf("Removed %d jobs", count($jobs)));
		    send_end_tag();
		} elseif($_SERVER['REQUEST_METHOD'] == "GET") {
		    send_start_tag("success", "link");
		    send_link(sprintf("%s/queue/all?format=list", $request->base), "link");
		    send_link(sprintf("%s/queue/all?format=data", $request->base), "job");
		    send_end_tag();
		}
	    }
	    break;
	 case "sort":
	    if(isset($request->childs[1])) {
		if(isset($request->childs[2])) {
		    send_queue_helper($request, $request->childs[2], $request->childs[1], "all");
		} elseif(isset($request->format)) {
		    send_queue_helper($request, $request->format, $request->childs[1], "all");
		} else {
		    send_start_tag("success", "link");
		    send_link(sprintf("%s/queue/sort/%s?format=list", 
				      $request->base,
				      $request->childs[1]), "link");
		    send_link(sprintf("%s/queue/sort/%s?format=data", 
				      $request->base, 
				      $request->childs[1]), "job");
		    send_end_tag();
		}
	    } else {
		send_start_tag("success", "link");
		send_link(sprintf("%s/queue/sort/none", $request->base), "link");
		send_link(sprintf("%s/queue/sort/started", $request->base), "link");
		send_link(sprintf("%s/queue/sort/jobid", $request->base), "link");
		send_link(sprintf("%s/queue/sort/state", $request->base), "link");
		send_link(sprintf("%s/queue/sort/name", $request->base), "link");
		send_end_tag();
	    }
	    break;
	 case "filter":
	    if(isset($request->childs[1])) {
		if(isset($request->childs[2])) {
		    send_queue_helper($request, $request->childs[2], "none", $request->childs[1]);
		} elseif(isset($request->format)) {
		    send_queue_helper($request, $request->format, "none", $request->childs[1]);
		} else {
		    send_start_tag("success", "link");
		    send_link(sprintf("%s/queue/filter/%s?format=list", 
				      $request->base,
				      $request->childs[1]), "link");
		    send_link(sprintf("%s/queue/filter/%s?format=data", 
				      $request->base, 
				      $request->childs[1]), "job");
		    send_end_tag();
		}
	    } else {
		send_start_tag("success", "link");
		send_link(sprintf("%s/queue/filter/all", $request->base), 
			  array("get" => "link", "delete" => "status"));
		send_link(sprintf("%s/queue/filter/waiting", $request->base), 
			  array("get" => "link", "delete" => "status"));
		send_link(sprintf("%s/queue/filter/pending", $request->base), 
			  array("get" => "link", "delete" => "status"));
		send_link(sprintf("%s/queue/filter/running", $request->base), 
			  array("get" => "link", "delete" => "status"));
		send_link(sprintf("%s/queue/filter/finished", $request->base), 
			  array("get" => "link", "delete" => "status"));
		send_link(sprintf("%s/queue/filter/warning", $request->base), 
			  array("get" => "link", "delete" => "status"));
		send_link(sprintf("%s/queue/filter/error", $request->base), 
			  array("get" => "link", "delete" => "status"));
		send_link(sprintf("%s/queue/filter/crashed", $request->base), 
			  array("get" => "link", "delete" => "status"));
		send_end_tag();
	    }
	    break;
	 default:
	    if(isset($request->childs[1])) {
		if($_SERVER['REQUEST_METHOD'] == "DELETE") {
		    if(!ws_dequeue($request->childs[0],
				   $request->childs[1])) {
			send_error(WS_ERROR_FAILED_CALL_METHOD, get_last_error());
		    }
		    send_start_tag("success", "link");
		    send_status(sprintf("Removed job %s", $request->childs[1]));
		    send_end_tag();
		} elseif($_SERVER['REQUEST_METHOD'] == "GET") {
		    // 
		    // Send a single job:
		    // 
		    $jobs = array();
		    if(!ws_queue($jobs)) {
			send_error(WS_ERROR_FAILED_CALL_METHOD, get_last_error());
		    }
		    send_start_tag("success", "job");
		    foreach($jobs as $result => $job) {
			if($request->childs[0] == $result &&
			   $request->childs[1] == $job['jobid']) {
			    $job['result'] = $result;
			    send_job($job, $request);
			}
		    }
		    send_end_tag();
		}
	    } else {
		send_error(WS_ERROR_MISSING_PARAMETER, null);
	    }
	    break;
	}
    } else {
	if($_SERVER['REQUEST_METHOD'] == "PUT" ||
	   $_SERVER['REQUEST_METHOD'] == "POST") {
	    $jobs = array();
	    $data = null;
	    if(!ws_enqueue($data, $jobs)) {
		send_error(WS_ERROR_FAILED_CALL_METHOD, get_last_error());
	    }
	    send_start_tag("success", "link");
	    if(count($jobs) > 1) {
		send_status(sprintf("Successful enqueued %d jobs", count($jobs)));
	    } else {
		send_status(sprintf("Successful enqueued new job (%d)", $jobs[0]['jobid']));
	    }
	    send_end_tag();
	} else {
	    // 
	    // Send all top nodes (links).
	    // 
	    send_start_tag("success", "link");
	    send_link(sprintf("%s/queue/all", $request->base), array("get" => "link", "delete" => "status"));
	    send_link(sprintf("%s/queue/sort", $request->base), "link");
	    send_link(sprintf("%s/queue/filter", $request->base), "link");
	    send_end_tag();
	}
    }
}

// 
// The result method (opendir, readdir and fopen).
// 
function send_result($request)
{
    if(!isset($request->childs)) {
	$out = array();
	if(!ws_opendir($out)) {
	    send_error(WS_ERROR_FAILED_CALL_METHOD, get_last_error());
	}
	send_start_tag("success", "link");
	foreach($out as $result => $jobid) {
	    send_link(sprintf("%s/result/%s/%s", 
			      $request->base,
			      $result, 
			      $jobid),
		      "link");
	}
	send_end_tag();
    } elseif(count($request->childs) == 2) {
	$out = array();
	if(!ws_readdir($request->childs[0], 
		       $request->childs[1], 
		       $out)) {
	    send_error(WS_ERROR_FAILED_CALL_METHOD, get_last_error());
	}
	send_start_tag("success", "link");
	foreach($out as $file) {
	    send_link(sprintf("%s/result/%s/%s/%s", 
			      $request->base,
			      $request->childs[0], 
			      $request->childs[1], 
			      $file),
		      "file");
	}
	send_end_tag();
    } elseif(count($request->childs) == "3") {
	$filename = "";
	if(!ws_fopen($request->childs[0], 
		     $request->childs[1], 
		     $request->childs[2], 
		     $filename)) {
	    send_error(WS_ERROR_FAILED_CALL_METHOD, get_last_error());
	}
	if(!file_exists($filename)) {
	    send_error(WS_ERROR_INVALID_REQUEST, get_last_error());
	} else {
	    send_start_tag("success", "file");
	    if(WS_FOPEN_RETURN_FORMAT == "base64") {
		printf("<tns:file encoding=\"base64\">%s</tns:file>\n", 
		       base64_encode(file_get_contents($filename)));
	    } else {
		printf("<tns:file encoding=\"binary\">%s</tns:file>\n", 
		       file_get_contents($filename));
	    }
	    send_end_tag();
	}
    } else {
	send_error(WS_ERROR_INVALID_REQUEST, null);
    }
}

// 
// Send response for request.
// 
function send_response($request)
{
    switch($request->method) {
     case "errors":
	send_errors($request);
	break;
     case "suspend":
	send_suspend($request);
	break;
     case "resume":
	send_resume($request);
	break;
     case "queue":
	send_queue($request);
	break;
     case "result":
	send_result($request);
	break;
     case "watch":
	send_error(WS_ERROR_UNEXPECTED_METHOD, "method watch is not yet implemented");
	break;
     default:
	if(isset($request->method)) {
	    send_error(WS_ERROR_UNEXPECTED_METHOD, null);
	} else {
	    send_root($request);
	}
    }
}

printf("<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n");

// 
// Receive request:
// 
$request = decode_request();

// 
// Send response:
// 
send_response($request);

?>