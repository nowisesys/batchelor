<?php

// -------------------------------------------------------------------------------
//  Copyright (C) 2007-2008 Anders Lövgren
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
// Web service support functions that gets called from scripts 
// under ws/http (lightweight) or ws/xmlrpm (apache org spec).
// 
// All functions should return false on error and set the error 
// message by calling put_error().
// 

define ("WS_RPC_METHOD_INFO", 0);
define ("WS_RPC_METHOD_FUNC", 1);
define ("WS_RPC_METHOD_SUSPEND", 2);
define ("WS_RPC_METHOD_RESUME", 3);
define ("WS_RPC_METHOD_ENQUEUE", 4);
define ("WS_RPC_METHOD_DEQUEUE", 5);
define ("WS_RPC_METHOD_QUEUE",   6);
define ("WS_RPC_METHOD_OPENDIR", 7);
define ("WS_RPC_METHOD_READDIR", 8);
define ("WS_RPC_METHOD_FOPEN",   9);

// 
// Returns the method entry based on its index number. Returns the whole
// array if called without an argument.
// 
function ws_get_rpc_method($index = -1)
{
    // 
    // RPC-method list for request broker. This structure list all RPC 
    // functions (command based and internal) and their parameter types.
    // 
    $functions = array( 
			array( 
			       "func" => null,
			       "name" => "info",
			       "long" => "Batchelor.ShowAPI",
			       "desc" => "Show RPC method API",
			       "params" => null,
			       "script" => "api.php"
			       ),
			array( 
			       "func" => null,
			       "name" => "func",
			       "long" => "batchelor.ShowFunction",
			       "desc" => "Show RPC method", 
			       "params" => array( "name" => "string" ),
			       "script" => "api.php"
			       ),
			array( 
			       "func" => "ws_suspend",
			       "name" => "suspend",
			       "long" => "Batchelor.SuspendRunningJob",
			       "desc" => "Suspend an already running job",
			       "params" => array( "result" => "string", 
						  "jobid"  => "string" ),
			       "script" => "suspend.php"
			       ),
			array( 
			       "func" => "ws_resume",
			       "name" => "resume",
			       "long" => "Batchelor.ResumePausedJob",
			       "desc" => "Resume a job thats in paused or stopped state",
			       "params" => array( "result" => "string",
						  "jobid"  => "string"),
			       "script" => "resume.php"
			       ),
			array( 
			       "func" => "ws_enqueue",
			       "name" => "enqueue",
			       "long" => "Batchelor.StartNewJob", 
			       "desc" => "Enqueue and start new job",
			       "params" => array( "indata" => "base64" ),
			       "script" => "enqueue.php"
			       ),
			array( 
			       "func" => "ws_dequeue",
			       "name" => "dequeue",
			       "long" => "Batchelor.DeleteJob",
			       "desc" => "Delete an running or finished job",
			       "params" => array( "result" => "string", 
						  "jobid" => "string" ),
			       "script" => "dequeue.php"
			   ),
			array( 
			       "func" => "ws_queue",
			       "name" => "queue",
			       "long" => "Batchelor.ListQueuedJobs",
			       "desc" => "List queued and finished jobs",
			       "params" => array( "sort" => "string",
						  "filter" => "string" ),
			       "script" => "queue.php"
			       ),
			array( 
			       "func" => "ws_result",
			       "name" => "opendir",
			       "long" => "Batchelor.ListResultDirs",
			       "desc" => "List result directories",
			       "params" => null,
			       "script" => "result.php"
			       ),
			array( 
			       "func" => "ws_result",
			       "name" => "readdir",
			       "long" => "Batchelor.ListResultFiles",
			       "desc" => "List files in result directory",
			       "params" => array( "result" => "string",
						  "jobid"  => "string" ),
			       "script" => "result.php"
			       ),
			array( 
			       "func" => "ws_result",
			       "name" => "fopen",
			       "long" => "Batchelor.GetResultFile",
			       "desc" => "Get content of result file",
			       "params" => array( "result" => "string",
						  "jobid"  => "string",
						  "file"   => "string" ),
			       "script" => "result.php"
			       )
			);
    
    if($index < 0) {
	return $functions;
    } else if(isset($functions[$index])) {
	return $functions[$index];
    } 
    
    // 
    // Return null if no function exists.
    // 
    return null;
}

// 
// Suspend (pause) the job. Requires job control to be enabled in the
// system configuration (conf/config.inc).
// 
function ws_suspend($result, $jobid)
{
    put_error("Not yet implemented");
    return false;
}

// 
// Resume (continue) the job. Requires job control to be enabled in the
// system configuration (conf/config.inc).
// 
function ws_resume($result, $jobid)
{
    put_error("Not yet implemented");
    return false;
}

// 
// Enqueue (starts) a new job. If indata is null, then its assumed that
// indata comes from an uploaded file (set in $_FILES). The $job variable
// gets filled with data about the newly enqueued job.
// 
function ws_enqueue(&$indata, &$job)
{
    if(!enqueue_job($indata, $job)) {
	return false;
    }
    return true;
}

// 
// Dequeue (stop and removes) a job.
// 
function ws_dequeue($result, $jobid)
{
    if(!delete_single_job($hostid, $result, $jobid)) {
	return false;
    }
    return true;
}

// 
// List queued jobs. The $sort and $filter argument can be used to
// limit what jobs gets returned.
// 
function ws_queue(&$jobs, $sort = "none", $filter = "all")
{
    $jobs = get_jobs($sort, $filter);
    if($jobs == false) {
	return false;
    }
    return true;
}

// 
// Provides access to the result directory. If $jobid is null, then a list
// of all job directories is returned. If $jobid is not null, then all files
// from that job directory is returned. If $file is set, then $result is
// set to the absolute path of the file (this allowes caller e.g. to use the 
// fast readfile() for pipelining the file content).
// 
// The result is returned in the $result parameter.
// 
// result(null, null, null, $result)        : get all job directories
// result($result, $jobid, null, $result)   : get content of this job directory
// result($result, $jobid, $file, $result)  : absolute path of file.
// 
function ws_result($result, $jobid, $file, &$result)
{
    return true;
}

// 
// The error handler for the HTTP web service.
// 
function ws_http_error_handler($code, $hint)
{    
    $head = null;
    
    switch($code) {
     case 304:
	$head = "HTTP/1.0 304 Not Modified (failed)";
	break;
     case 400:
	$head = "HTTP/1.0 400 Bad Request";
	break;
     case 403:
	$head = "HTTP/1.0 403 Forbidden";
	break;
     case 404:
	$head = "HTTP/1.0 404 Not Found";
	break;
     default:
	$head = "HTTP/1.0 500 Internal Server Error";
	$code = 500;
	break;
    }
    
    // 
    // Send the HTTP status code and if the call has failed with errors, then
    // encode the error string in the HTTP header.
    // 
    header($head);
    // if(has_errors()) {
    // 	header(sprintf("Reason: %s", get_last_error()));
    // }
    header("Connection: close");

    if(defined("WS_HTTP_SEND_ERROR_BODY") && WS_HTTP_SEND_ERROR_BODY) {
	printf("<html><head><title>HTTP Error %d</title></head><body><h2>%s</h2><hr>%s</body></html>\n", 
	       $code, $head, $hint);
    }
    exit(1);
}

//
// Get MIME type of file pointed to by path. At the moment its just a 
// stub for future extension (the application/octet-stream MIME type means 
// unknonw filetype).
// 
function ws_get_mime_type($path)
{
    return "application/octet-stream";
}

//
// This function should be called from all HTTP web service scripts to
// validate required parameters, set hostid global variable
// 
function ws_http_session_setup($params = null)
{
    // 
    // Make sure this script is allowed to be runned:
    // 
    if(!defined("WS_ENABLE_HTTP")) {
	ws_http_error_handler(403, "This script is not configured (see conf/config.inc)");
    }
    if(!WS_ENABLE_HTTP) {
	ws_http_error_handler(403, "This script is disallowed by the configuration (see conf/config.inc)");
    }

    // 
    // Check required parameters.
    // 
    if(isset($params)) {
	foreach($params as $param) {
	    if(!isset($_REQUEST[$param])) {
		ws_http_error_handler(400, sprintf("The <u>%s</u> parameter is missing", $param));
	    }
	}
    }

    // 
    // Handle hostid
    // 
    if(defined("WS_HTTP_HOSTID_SOURCE")) {
	$source = WS_HTTP_HOSTID_SOURCE;
    } else if(defined("WS_HOSTID_SOURCE")) {
	$source = WS_HOSTID_SOURCE;
    } else {
	ws_http_error_handler(500, "No hostid source defined in configuration (see conf/config.inc)");
    }
    
    switch($source) {
     case "param":                           // request param
	$GLOBALS['hostid'] = $_REQUEST['hostid'];      
	break;
     case "cookie":                          // use cookie
	if(!isset($_COOKIES['hostid'])) {
	    ws_http_error_handler(400, "The <u>hostid</u> cookie is unset");
	}
	$GLOBALS['hostid'] = $_COOKIES['hostid'];
	break;
     case "auto":                            // set cookie
	update_hostid_cookie();
	break;
    }
    if(!isset($GLOBALS['hostid'])) {
	ws_http_error_handler(400, "The <u>hostid</u> variable is missing");
    }
}

?>
