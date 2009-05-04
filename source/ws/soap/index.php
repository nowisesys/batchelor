<?php

// -------------------------------------------------------------------------------
//  Copyright (C) 2007-2009 Anders Lövgren
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
// This is the server side code implementing a SOAP service.
// 

// 
// Both input and output for some methods are complex types (objects), so
// we must support it here also. We have (at least) two ways to do that. 
// Either we use associative arrays with keys matching the names as returned
// by $client->__getTypes() or we need to define classes with members having
// thoose names. The following example shows two possible implementations 
// for the version method.
// 
// // 
// // Example: Using array as response type
// // 
// function version() {
//   return array("versionReturn" => "1.0");
// }
// 
// // 
// // Example: Using class as response type
// // 
// class VersionResponse {
//   var $versionReturn;
//   function VersionResponse() {
//     $this->versionReturn = WS_SOAP_INTERFACE_VERSION;
//   }
// }
// function version() {
//   return new VersionResponse("1.0");
// }
// 
// Even though using arrays would be a simple solution, it's not our prefered
// way of doing things. The main objection against it is that it doesn't 
// preserve the object oriented approach, even though arrays and objects are 
// somewhat related to each other using code like: $obj = (object)$arr
// 
// All request/response classes can be found in include/soap.inc for 
// thoose who like to write their own clients.
// 

ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . "../../../");

//
// Get configuration.
// 
include "conf/config.inc";

include "include/common.inc";
include "include/queue.inc";
include "include/ws.inc";
include "include/delete.inc";
include "include/soap.inc";

// ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache during testing

// 
// The SOAP handler class.
// 
class batchelor {
    // 
    // output: VersionResponse(version=string)
    // 
    function version()
    {
	return new VersionResponse(WS_SOAP_INTERFACE_VERSION);
    }
    
    // 
    // input:  EnqueueParams(indata=string)
    // output: EnqueueRepsonse(EnqueueResult[])
    // 
    function enqueue($obj)
    {
	if(!isset($obj) || !isset($obj->indata)) {
	    send_error(WS_ERROR_MISSING_PARAMETER);
	}
	if(strlen($obj->indata) == 0) {
	    send_error(WS_ERROR_INVALID_FORMAT);
	}
	$jobs = array();
	if(!ws_enqueue($obj->indata, $jobs)) {
	    send_error(WS_ERROR_FAILED_CALL_METHOD);
	}
	
	$result = array();
	foreach($jobs as $job) {
	    array_push($result, new EnqueueResult($job['date'], $job['jobid'], 
						  $job['result'], $job['stamp'], 
						  $job['time']));
	}	
	return new EnqueueResponse($result);
    }

    // 
    // input:  QueueParams(sort=string, filter=string)
    // output: QueueResponse(QueuedJob[])
    // 
    function queue($obj)
    {	
	$args = new QueueParams("none", "all");
	$jobs = array();
	
	if(isset($obj->sort) && strlen($obj->sort) != 0) {
	    $args->sort = strtolower($obj->sort);
	}
	if(isset($obj->filter) && strlen($obj->filter) != 0) {
	    $args->filter = strtolower($obj->filter);
	}
	if($args->sort == "job_id") {
	    $args->sort = "jobid";
	}
	
	if(!ws_queue($jobs, $args->sort, $args->filter)) {
	    send_error(WS_ERROR_FAILED_CALL_METHOD);
	}
	
	$result = array();
	foreach($jobs as $resdir => $job) {
	    array_push($result, new QueuedJob(new JobIdentity($job['jobid'], $resdir), $job['state']));
	}	
	return new QueueResponse($result);
    }
    
    // 
    // input:  ResumeParams(jobIdentity)
    // output: ResumeResponse(bool)
    // 
    function resume($obj)
    {	
	// 
	// Don't allow the method call unless job control is enabled.
	// 
	if(!defined("ENABLE_JOB_CONTROL") || ENABLE_JOB_CONTROL == "off") {
	    send_error(WS_ERROR_DISALLOWED);
	}
	
	if(!isset($obj) || !isset($obj->job) || !isset($obj->job->jobID) || !isset($obj->job->result)) {
	    send_error(WS_ERROR_MISSING_PARAMETER);
	}
	if(strlen($obj->job->jobID) == 0 || strlen($obj->job->result) == 0) {
	    send_error(WS_ERROR_INVALID_FORMAT);
	}
	
	$result = ws_resume($obj->job->result, $obj->job->jobID);
	return new ResumeResponse($result);
    }

    // 
    // input:  SuspendParams(jobIdentity)
    // output: SuspendResponse(bool)
    // 
    function suspend($obj)
    {
	// 
	// Don't allow the method call unless job control is enabled.
	// 
	if(!defined("ENABLE_JOB_CONTROL") || ENABLE_JOB_CONTROL == "off") {
	    send_error(WS_ERROR_DISALLOWED);
	}
	
	if(!isset($obj) || !isset($obj->job) || !isset($obj->job->jobID) || !isset($obj->job->result)) {
	    send_error(WS_ERROR_MISSING_PARAMETER);
	}
	if(strlen($obj->job->jobID) == 0 || strlen($obj->job->result) == 0) {
	    send_error(WS_ERROR_INVALID_FORMAT);
	}
	
	$result = ws_suspend($obj->job->result, $obj->job->jobID);
	return new SuspendResponse($result);
    }
    
    // 
    // input:  DequeueParams(jobIdentity)
    // output: DequeueResponse(boolean)
    // 
    function dequeue($obj)
    {
	if(!isset($obj) || !isset($obj->job) || !isset($obj->job->jobID) || !isset($obj->job->result)) {
	    send_error(WS_ERROR_MISSING_PARAMETER);
	}
	
	if(strlen($obj->job->jobID) == 0 || strlen($obj->job->result) == 0) {
	    send_error(WS_ERROR_INVALID_FORMAT);
	}
	
	$result = ws_dequeue($obj->job->result, $obj->job->jobID);	
	return new DequeueResponse($result);
    }

    // 
    // input:  WatchParams(stamp=int)
    // output: WatchResponse(QueuedJob[])
    // 
    function watch($obj)
    {
	$jobs = array();
	if(!isset($obj) || !isset($obj->stamp)) {
	    send_error(WS_ERROR_MISSING_PARAMETER);
	}
	if(!ws_watch($jobs, $obj->stamp)) {
	    send_error(WS_ERROR_FAILED_CALL_METHOD);
	}
	$result = array();
	foreach($jobs as $resdir => $job) {
	    array_push($result, new QueuedJob(new JobIdentity($job['jobid'], $resdir), $job['state']));
	}
	return new WatchResponse($result);
    }

    // 
    // output: OpendirResponse(jobIdentity[])
    // 
    function opendir()
    {
	$jobs = array();
	if(!ws_opendir($jobs)) {
	    send_error(WS_ERROR_FAILED_CALL_METHOD);
	}
	$result = array();
	foreach($jobs as $resdir => $job) {
	    array_push($result, new JobIdentity($job, $resdir));
	}
	return new OpendirResponse($result);
    }

    // 
    // input:  ReaddirParams(jobIdentity)
    // output: ReaddirResponse(string[])     // List of files including subdirs
    // 
    function readdir($obj)
    {
	if(!isset($obj) || !isset($obj->job) || !isset($obj->job->jobID) || !isset($obj->job->result)) {
	    send_error(WS_ERROR_MISSING_PARAMETER);
	}
	if(strlen($obj->job->jobID) == 0 || strlen($obj->job->result) == 0) {
	    send_error(WS_ERROR_INVALID_FORMAT);
	}
	
	$result = array();
	ws_readdir($obj->job->result, $obj->job->jobID, $result);
	return new ReaddirResponse($result);
    }
    
    // 
    // input:  FopenParams(jobIdentity, file=string)
    // output: FopenResponse(base64Binary)   // The file content.
    // 
    function fopen($obj)
    {
	if(!isset($obj) || !isset($obj->job) || !isset($obj->job->jobID) || !isset($obj->job->result) || !isset($obj->file)) {
	    send_error(WS_ERROR_MISSING_PARAMETER);
	}
	if(strlen($obj->job->jobID) == 0 || strlen($obj->job->result) == 0 || strlen($obj->file) == 0) {
	    send_error(WS_ERROR_INVALID_FORMAT);
	}
	$path = "";
	if(!ws_fopen($obj->job->result, $obj->job->jobID, $obj->file, $path)) {
	    send_error(WS_ERROR_INVALID_REQUEST);
	}
	return new FopenResponse(file_get_contents($path));
    }
    
    // 
    // input:  StatParams(jobIdentity)
    // output: StatResponse(queuedJob)
    // 
    function stat($obj) 
    {
	if(!isset($obj) || !isset($obj->job) || !isset($obj->job->jobID) || !isset($obj->job->result)) {
	    send_error(WS_ERROR_MISSING_PARAMETER);
	}
	if(strlen($obj->job->jobID) == 0 || strlen($obj->job->result) == 0) {
	    send_error(WS_ERROR_INVALID_FORMAT);
	}
	$result = array();
	ws_stat($obj->job->result, $obj->job->jobID, $result);
	return new StatResponse(new QueuedJob($obj->job, $result['state']));
    }
}

// 
// This function gets called to report error. It will also terminate
// execution of the current script.
// 
// See: http://www.w3.org/TR/2000/NOTE-SOAP-20000508/#_Toc478383510
// 
function send_error($code)
{
    // 
    // Write fault reason to error log:
    // 
    error_log(sprintf("SOAP call error: %d (%s)", $code, get_error($code)));
    
    // 
    // Send SOAP fault code to client:
    // 
    if($code == WS_ERROR_FAILED_CALL_METHOD) {
	throw new SoapFault("Server", get_error($code));
    } else {
	throw new SoapFault("Client", get_error($code));
    }
    
    // 
    // I assume that terminate the script is the right thing 
    // to do here.
    // 
    exit(1);
}

//
// Setup the SOAP session.
// 
ws_soap_session_setup();

// 
// Initilize SOAP library with the WSDL:
// 
$server = new SoapServer(get_wsdl_url());
if(!$server) {
    error_log("Failed create SOAP server");
    send_error(WS_ERROR_MISSING_EXTENSION);
}
$server->setClass("batchelor");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $server->handle();
} else {
    echo "The SOAP service provides the following functions:<br />";
    $functions = $server->getFunctions();
    foreach($functions as $func) {
	echo $func . "<br />\n";
    }
}

?>
