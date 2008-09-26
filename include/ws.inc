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

// 
// RPC method indexes:
// 
define ("WS_RPC_METHOD_INFO",    0);
define ("WS_RPC_METHOD_FUNC",    1);
define ("WS_RPC_METHOD_DOCS",    2);
define ("WS_RPC_METHOD_SUSPEND", 3);
define ("WS_RPC_METHOD_RESUME",  4);
define ("WS_RPC_METHOD_ENQUEUE", 5);
define ("WS_RPC_METHOD_DEQUEUE", 6);
define ("WS_RPC_METHOD_QUEUE",   7);
define ("WS_RPC_METHOD_OPENDIR", 8);
define ("WS_RPC_METHOD_READDIR", 9);
define ("WS_RPC_METHOD_FOPEN",  10);

// 
// Error codes (encoded in HTTP header):
// 
define ("WS_ERROR_MISSING_PARAMETER",  1);     // Missing parameter for script.
define ("WS_ERROR_INVALID_FORMAT",     2);     // Invalid or unsupported format.
define ("WS_ERROR_UNEXPECTED_METHOD",  3);     // No such method.
define ("WS_ERROR_MISSING_EXTENSION",  4);     // Can't continue due to server problem.
define ("WS_ERROR_REQUEST_BROKER",     5);     // Failed map logical name to script.
define ("WS_ERROR_FAILED_CALL_METHOD", 6);     // Failed call function ws_xxx().
define ("WS_ERROR_CONFIG_PROBLEM",     7);     // Server is not configured.
define ("WS_ERROR_DISALLOWED",         8);     // Calling RPC interface is not allowed.
define ("WS_ERROR_INVALID_REQUEST",    9);     // Missmatch between script/method name and/or parameters.

// 
// Return all error messages or a specific error message.
// 
function get_error($code = null)
{
    $errors = array( "Missing parameter for script",
		     "Invalid or unsupported format",
		     "No such method",
		     "Can't continue due to server problem",
		     "Failed map logical name to script",
		     "Failed call function associated function",
		     "Server is not configured",
		     "Calling this RPC interface is not allowed",
		     "Missmatch between script/method name and/or parameters" );
    
    if(isset($code)) {
	if($code >= 1 && $code <= count($errors)) {
	    return $errors[$code - 1];        // error codes starts at 1
	} else {
	    return null;
	}
    } else {
	return $errors;
    }
}

// 
// Returns the method entry based on its logical name.
// 
function ws_get_rpc_method_by_name($name) 
{
    $names = array( "info", "func", "docs", "errors", "errmsg", "suspend", "resume", "enqueue", "dequeue", "queue", "opendir", "readdir", "fopen" );
    for($i = 0; $i < count($names); $i++) {
	if($names[$i] == $name) {
	    return ws_get_rpc_method_by_index($i);
	}
    }
    return null;
}

// 
// Returns the method entry based on its index number. Returns the whole
// array if called without an argument.
// 
function ws_get_rpc_method_by_index($index = -1)
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
			       "desc" => "List all methods thats part of the API. This method returns an array of strings.",
			       "params" => null,
			       "return" => "array=[string]",
			       "script" => "api.php"
			       ),
			array( 
			       "func" => null,
			       "name" => "func",
			       "long" => "batchelor.ShowFunction",
			       "desc" => "Show detailed information about a single RPC method. This method returns an object (with mixed values) describing the method.", 
			       "params" => array( "name" => "string" ),
			       "return" => "object=()",
			       "script" => "api.php"
			       ),
			array( 
			       "func" => null,
			       "name" => "docs",
			       "long" => "Batchelor.ShowManual",
			       "desc" => "Show the HTTP RPC method API as manual. This method can only be used with HTML format.",
			       "params" => null,
			       "return" => null,
			       "script" => "api.php"
			       ),
			array( 
			       "func" => null,
			       "name" => "errors",
			       "long" => "Batchelor.GetErrorCollection",
			       "desc" => "Return an collection of all errors defined (codes and messages).",
			       "params" => null,
			       "return" => "array=[object(integer,string)]",
			       "script" => "error.php"
			       ),
			array( 
			       "func" => null,
			       "name" => "errmsg",
			       "long" => "Batchelor.GetErrorMessage",
			       "desc" => "Return error message string for an given error code.",
			       "params" => array( "code" => "integer" ),
			       "return" => "string",
			       "script" => "error.php"
			       ),
			array( 
			       "func" => "ws_suspend",
			       "name" => "suspend",
			       "long" => "Batchelor.SuspendRunningJob",
			       "desc" => "Suspend an already running job. Returns true on success and false on failure.",
			       "params" => array( "result" => "string", 
						  "jobid"  => "string" ),
			       "return" => "boolean",
			       "script" => "suspend.php"
			       ),
			array( 
			       "func" => "ws_resume",
			       "name" => "resume",
			       "long" => "Batchelor.ResumePausedJob",
			       "desc" => "Resume a job thats in paused or stopped state. Returns true on success and false on failure.",
			       "params" => array( "result" => "string",
						  "jobid"  => "string"),
			       "return" => "boolean",
			       "script" => "resume.php"
			       ),
			array( 
			       "func" => "ws_enqueue",
			       "name" => "enqueue",
			       "long" => "Batchelor.StartNewJob", 
			       "desc" => "Enqueue and start new job. Returns array of job ID:s for the newly started jobs.",
			       "params" => array( "indata" => "string" ),
			       "return" => "array=[integer]",
			       "script" => "enqueue.php"
			       ),
			array( 
			       "func" => "ws_dequeue",
			       "name" => "dequeue",
			       "long" => "Batchelor.DeleteJob",
			       "desc" => "Delete an running or finished job. Returns true on success and false on failure.",
			       "params" => array( "result" => "string", 
						  "jobid" => "string" ),
			       "return" => "boolean",
			       "script" => "dequeue.php"
			   ),
			array( 
			       "func" => "ws_queue",
			       "name" => "queue",
			       "long" => "Batchelor.ListQueuedJobs",
			       "desc" => "List queued and finished jobs. Returns an array of objects describing the jobs and their state.",
			       "params" => array( "sort" => "string",
						  "filter" => "string" ),
			       "return" => "array=[object]",
			       "script" => "queue.php"
			       ),
			array( 
			       "func" => "ws_result",
			       "name" => "opendir",
			       "long" => "Batchelor.ListResultDirs",
			       "desc" => "List result directories. Returns an list of directory names.",
			       "params" => null,
			       "return" => "array=[string]",
			       "script" => "result.php"
			       ),
			array( 
			       "func" => "ws_result",
			       "name" => "readdir",
			       "long" => "Batchelor.ListResultFiles",
			       "desc" => "List files in a specific result directory.",
			       "params" => array( "result" => "string",
						  "jobid"  => "string" ),
			       "return" => "array=[string]",
			       "script" => "result.php"
			       ),
			array( 
			       "func" => "ws_result",
			       "name" => "fopen",
			       "long" => "Batchelor.GetResultFile",
			       "desc" => "Get content of an result file. The content is returned with MIME-type application/octet-stream (binary format).",
			       "params" => array( "result" => "string",
						  "jobid"  => "string",
						  "file"   => "string" ),
			       "return" => "string",
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
function ws_http_error_handler($code, $error)
{    
    $head = null;
    
    switch($code) {
     case 304:            // Should no longer be used by the HTTP RPC code
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
     case 409:            // Used for signal failure to clients.
	$head = "HTTP/1.0 409 Conflict";
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
    header(sprintf("X-RPC-Error: %d", $error));
    header("Connection: close");
    
    if(!has_errors()) {
	put_error("Unknown error (no error message set)");
    }
    if(defined("WS_HTTP_SEND_ERROR_BODY") && WS_HTTP_SEND_ERROR_BODY) {
	printf("<html><head><title>HTTP Error %d</title></head><body><h2>%s</h2><hr>%s</body></html>\n", 
	       $code, $head, get_last_error());
    }
    exit(1);
}

//
// Get MIME type of file pointed to by path. At the moment its just a 
// stub for future extension (the application/octet-stream MIME type means 
// unknonw filetype). 
// 
// If path is null, then the mime-type is determined from the global format
// parameter that is influenced by the request parameter with same name.
// 
function ws_get_mime_type($path = null)
{
    if(isset($path)) {
	return "application/octet-stream";
    } else {
	if($GLOBALS['format'] == "xml") {
	    return "text/xml";
	} else if($GLOBALS['format'] == "foa") {
	    return "text/x-csv";
	} else if($GLOBALS['format'] == "html") {
	    return "text/html";
	} else if($GLOBALS['format'] == "human") {
	    return "text/plain";
	}
    }
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
	put_error("This script is not configured (see conf/config.inc)");
	ws_http_error_handler(403, WS_ERROR_CONFIG_PROBLEM);
    }
    if(!WS_ENABLE_HTTP) {
	put_error("This script is disallowed by the configuration (see conf/config.inc)");
	ws_http_error_handler(403, WS_ERROR_CONFIG_PROBLEM);
    }

    // 
    // Check that output buffering exists.
    // 
    if(!function_exists("ob_start") || !function_exists("ob_end_flush")) {
	error_log("Missing support for output buffering using ob_start() and ob_end_flush()");
	put_error("No support for output buffering (see web server log)");
	ws_http_error_handler(500, WS_ERROR_MISSING_EXTENSION);
    }
    
    // 
    // Check required parameters.
    // 
    if(isset($params)) {
	foreach($params as $param) {
	    if(!isset($_REQUEST[$param])) {
		put_error(sprintf("The <u>%s</u> parameter is missing", $param));
		ws_http_error_handler(400, WS_ERROR_MISSING_PARAMETER);
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
	put_error("No hostid source defined in configuration (see conf/config.inc)");
	ws_http_error_handler(500, WS_ERROR_CONFIG_PROBLEM);
    }
    
    switch($source) {
     case "param":                           // request param
	$GLOBALS['hostid'] = $_REQUEST['hostid'];      
	break;
     case "cookie":                          // use cookie
	if(!isset($_COOKIES['hostid'])) {
	    put_error("The <u>hostid</u> cookie is unset");
	    ws_http_error_handler(400, WS_ERROR_MISSING_PARAMETER);
	}
	$GLOBALS['hostid'] = $_COOKIES['hostid'];
	break;
     case "auto":                            // set cookie
	update_hostid_cookie();
	break;
    }
    if(!isset($GLOBALS['hostid'])) {
	put_error("The <u>hostid</u> variable is missing");
	ws_http_error_handler(400, WS_ERROR_MISSING_PARAMETER);
    }
    
    if(defined("WS_HTTP_OUTPUT_FORMAT")) {
	$GLOBALS['format'] = WS_HTTP_OUTPUT_FORMAT;
    }
    if(isset($_REQUEST['format'])) {
	if($_REQUEST['format'] == "xml" || $_REQUEST['format'] == "foa") {
	    $GLOBALS['format'] = $_REQUEST['format'];
	} else if($_REQUEST['format'] == "plain" || $_REQUEST['format'] == "human") {
	    $GLOBALS['format'] = "human";
	} else if($_REQUEST['format'] == "html") {
	    $GLOBALS['format'] = "html";
	}
    }
    if(!isset($GLOBALS['format'])) {
	$GLOBALS['format'] = "foa";
    }
}

?>
