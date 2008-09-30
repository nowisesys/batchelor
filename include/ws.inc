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
			       "return" => "array=[object=(integer,string)]",
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
			       "desc" => "Enqueue and start new job. Returns a job objects for the newly started jobs.",
			       "params" => array( "indata" => "string" ),
			       "return" => "object=(jobid=integer,date=string,time=string,stamp=integer)",
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
			       "return" => "array=[object=()]",
			       "script" => "queue.php"
			       ),
			array( 
			       "func" => "ws_result",
			       "name" => "opendir",
			       "long" => "Batchelor.ListResultDirs",
			       "desc" => "List result directories. Returns an list of directory objects that can be used when calling the readdir method.",
			       "params" => null,
			       "return" => "array=[object=(result=string,jobid=string)]",
			       "script" => "result.php"
			       ),
			array( 
			       "func" => "ws_result",
			       "name" => "readdir",
			       "long" => "Batchelor.ListResultFiles",
			       "desc" => "List files in a specific result directory. Returns an list of all files in the specific result directory pointed out by the method parameters (result and jobid). Also see the opendir and fopen method",
			       "params" => array( "result" => "string",
						  "jobid"  => "string" ),
			       "return" => "array=[string]",
			       "script" => "result.php"
			       ),
			array( 
			       "func" => "ws_result",
			       "name" => "fopen",
			       "long" => "Batchelor.GetResultFile",
			       "desc" => "Get content of an result file. The content is returned with MIME-type application/octet-stream (binary format). Call the opendir method to obtain values for the result and jobid parameters.",
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
// Helper function for process control.
// 
function ws_signal_process($result, $jobid, $signal)
{
    $error = null;
    if(!process_control($GLOBALS['hostid'], $result, $jobid, $signal, &$error)) {
	switch($error['where']) {
	 case "resdir":
	    if(!isset($error['reason'])) {
		put_error("The result directory don't exist");
	    } else {
		put_error("Unknown error related to result directory");
	    }
	    break;
	 case "pid":
	    if($error['reason'] == "file") {
		put_error("The process identifier file is missing or invalid");
	    } else if($error['reason'] == "perm") {
		put_error("Failed suspend process (permission problem)");
	    } else if($error['reason'] == "proc") {
		put_error("The process was not found.");
	    } else {
		put_error("Unknown error when sending $signal signal to process");
	    }
	    break;
	 default:
	    put_error("Unhandled error occured in process control code.");
	}
	return false;
    }
    return true;
}

// 
// Suspend (pause) the job. Requires job control to be enabled in the
// system configuration (conf/config.inc).
// 
function ws_suspend($result, $jobid)
{
    return ws_signal_process($result, $jobid, "stop");
}

// 
// Resume (continue) the job. Requires job control to be enabled in the
// system configuration (conf/config.inc).
// 
function ws_resume($result, $jobid)
{
    return ws_signal_process($result, $jobid, "cont");
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
    if(!delete_single_job($GLOBALS['hostid'], $result, $jobid)) {
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
    $jobs = get_jobs($GLOBALS['hostid'], $sort, $filter);
    if($jobs == false) {
	return false;
    }
    return true;
}

// 
// Return an accossiative array of job directories where the key is the
// name of the directory (result) and the value is the jobid.
// 
function ws_opendir(&$out)
{
    $jobs = get_jobs($GLOBALS['hostid']);
    if($jobs == false) {
	return false;
    }
    foreach($jobs as $result => $job) {
	$out[$result] = $job['jobid'];
    }
    return true;
}

// 
// Helper function for ws_readdir.
// 
function readdir_add_files(&$out, $subdir)
{
    $handle = opendir($subdir);
    if($handle) {
	while(false !== ($file = readdir($handle))) {
	    if($file != "." && $file != "..") {
		$path = sprintf("%s/%s", $subdir, $file);
		if(is_dir($path)) {
		    if(!readdir_add_files(&$out, $path)) {
			return false;
		    }
		}
		else {
		    $out[] = substr($path, 2);   // Discard leading "./" in path
		}
	    }
	}
	closedir($handle);
    }
    return true;
}

// 
// Return all files from result directory.
// 
function ws_readdir($result, $jobid, &$out)
{
    if(!isset($result)) {
	put_error("Required argument result is unset");
	return false;
    }
    if(!isset($jobid)) {
	put_error("Required argument jobid is unset");
	return false;
    }
    
    $jobdir = sprintf("%s/jobs/%s/%s", CACHE_DIRECTORY, $GLOBALS['hostid'], $result);
    if(!file_exists($jobdir)) {
	put_error("The job directory %s don't exists", $result);
	return false;
    }
    $cwd = getcwd();
    if($cwd) {
	if(chdir($jobdir)) {
	    if(!readdir_add_files($out, ".")) {
		put_error("Failed read content of job directory");
		return false;
	    }
	} else {
	    put_error("Failed change working directory to job directory");
	    return false;
	}
	chdir($cwd);
    } else {
	put_error("Failed get path of current working directory");
	return false;
    }
    return true;
}

function ws_fopen($result, $jobid, $file, &$out)
{
    if(!isset($result)) {
	put_error("Required argument result is unset");
	return false;
    }
    if(!isset($jobid)) {
	put_error("Required argument jobid is unset");
	return false;
    }
    if(!isset($file)) {
	put_error("Required argument file is unset");
	return false;
    }
    $path = sprintf("%s/jobs/%s/%s/%s", CACHE_DIRECTORY, $GLOBALS['hostid'], $result, $file);
    if(!file_exists($path)) {
	put_error("The requested file don't exists", $result);
	return false;
    }
    $out = $path;
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
