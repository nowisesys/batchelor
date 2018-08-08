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
// Web service support functions that gets called from scripts 
// under ws/http (lightweight) or ws/xmlrpm (apache org spec).
// 
// All functions should return false on error and set the error 
// message by calling put_error().
// 
// 
// Determine the type of web service we provide:
// 
if (strstr($_SERVER['REQUEST_URI'], "ws/xmlrpc")) {
        define("WS_XMLRPC", 1);
} elseif (strstr($_SERVER['REQUEST_URI'], "ws/http")) {
        define("WS_HTTP", 1);
} elseif (strstr($_SERVER['REQUEST_URI'], "ws/rest")) {
        define("WS_REST", 1);
} elseif (strstr($_SERVER['REQUEST_URI'], "ws/soap")) {
        define("WS_SOAP", 1);
}

// 
// Set format for the fopen method (send file content)
// 
if (defined("WS_XMLRPC")) {
        if (!defined("WS_XMLRPC_FOPEN_BASE64_ENCODE")) {
                if (defined("WS_FOPEN_BASE64_ENCODE")) {
                        define("WS_XMLRPC_FOPEN_BASE64_ENCODE", WS_FOPEN_BASE64_ENCODE);
                } else {
                        define("WS_XMLRPC_FOPEN_BASE64_ENCODE", false);
                }
        }
        define("FOPEN_BASE64_ENCODE", WS_XMLRPC_FOPEN_BASE64_ENCODE);
} elseif (defined("WS_HTTP")) {
        if (!defined("WS_HTTP_FOPEN_BASE64_ENCODE")) {
                if (defined("WS_FOPEN_BASE64_ENCODE")) {
                        define("WS_HTTP_FOPEN_BASE64_ENCODE", WS_FOPEN_BASE64_ENCODE);
                } else {
                        define("WS_HTTP_FOPEN_BASE64_ENCODE", false);
                }
        }
        define("FOPEN_BASE64_ENCODE", WS_HTTP_FOPEN_BASE64_ENCODE);
} elseif (defined("WS_REST")) {
        if (!defined("WS_REST_FOPEN_BASE64_ENCODE")) {
                if (defined("WS_FOPEN_BASE64_ENCODE")) {
                        define("WS_REST_FOPEN_BASE64_ENCODE", WS_FOPEN_BASE64_ENCODE);
                } else {
                        if (isset($_REQUEST['encode']) && $_REQUEST['encode'] == "foa") {
                                define("WS_REST_FOPEN_BASE64_ENCODE", false);
                        } else {
                                define("WS_REST_FOPEN_BASE64_ENCODE", true);
                        }
                }
        }
        define("FOPEN_BASE64_ENCODE", WS_REST_FOPEN_BASE64_ENCODE);
} elseif (defined("WS_SOAP")) {
        if (!defined("WS_SOAP_FOPEN_BASE64_ENCODE")) {
                if (defined("WS_FOPEN_BASE64_ENCODE")) {
                        define("WS_SOAP_FOPEN_BASE64_ENCODE", WS_FOPEN_BASE64_ENCODE);
                } else {
                        define("WS_SOAP_FOPEN_BASE64_ENCODE", false);
                }
        }
        define("FOPEN_BASE64_ENCODE", WS_SOAP_FOPEN_BASE64_ENCODE);
} else {
        define("FOPEN_BASE64_ENCODE", false);
}
if (FOPEN_BASE64_ENCODE) {
        define("WS_FOPEN_RETURN_FORMAT", "base64");
} else {
        define("WS_FOPEN_RETURN_FORMAT", "string");
}

// 
// RPC method indexes:
// 
define("WS_RPC_METHOD_INFO", 0);
define("WS_RPC_METHOD_FUNC", 1);
define("WS_RPC_METHOD_DOCS", 2);
define("WS_RPC_METHOD_SUSPEND", 3);
define("WS_RPC_METHOD_RESUME", 4);
define("WS_RPC_METHOD_ENQUEUE", 5);
define("WS_RPC_METHOD_DEQUEUE", 6);
define("WS_RPC_METHOD_QUEUE", 7);
define("WS_RPC_METHOD_OPENDIR", 8);
define("WS_RPC_METHOD_READDIR", 9);
define("WS_RPC_METHOD_FOPEN", 10);
define("WS_RPC_METHOD_STAT", 11);

// 
// Error codes (encoded in HTTP header):
// 
define("WS_ERROR_MISSING_PARAMETER", 1);     // Missing parameter for script.
define("WS_ERROR_INVALID_FORMAT", 2);     // Invalid or unsupported format.
define("WS_ERROR_UNEXPECTED_METHOD", 3);     // No such method.
define("WS_ERROR_MISSING_EXTENSION", 4);     // Can't continue due to server problem.
define("WS_ERROR_REQUEST_BROKER", 5);     // Failed map logical name to script.
define("WS_ERROR_FAILED_CALL_METHOD", 6);     // Failed call function ws_xxx().
define("WS_ERROR_CONFIG_PROBLEM", 7);     // Server is not configured.
define("WS_ERROR_DISALLOWED", 8);     // Calling this RPC interface or method is not allowed.
define("WS_ERROR_INVALID_REQUEST", 9);     // Missmatch between script/method name and/or parameters.
define("WS_ERROR_REQUEST_METHOD", 10);     // Request method is not accepted.
define("WS_ERROR_NOT_IMPLEMENTED", 11);     // Method is not yet implemented/supported.
// 
// The various status of an web service interface.
// 
define("WS_STATUS_UNKNOWN", 0);
define("WS_STATUS_ENABLED", 1);
define("WS_STATUS_DISABLED", 2);
define("WS_STATUS_NOT_CONFIGURED", 3);
define("WS_STATUS_MISSING_EXTENSION", 4);

// 
// Return all error messages or a specific error message.
// 
function get_error($code = null)
{
        $errors = array(
                "Missing parameter for script",
                "Invalid or unsupported format",
                "No such method",
                "Can't continue due to server problem",
                "Failed map logical name to script",
                "Failed call function associated function",
                "Server is not configured",
                "Calling this RPC interface or method is not allowed",
                "Missmatch between script/method name and/or parameters",
                "Request method is not accepted",
                "Method is not yet implemented/supported"
        );

        if (isset($code)) {
                if ($code >= 1 && $code <= count($errors)) {
                        return $errors[$code - 1];        // error codes starts at 1
                } else {
                        return null;
                }
        } else {
                return $errors;
        }
}

// 
// Get URL of the web service directory.
// 
function get_server_url($resolve = true)
{
        // 
        // Default options:
        // 
        $opts = array(
                "scheme" => "http",
                "addr"   => "localhost",
                "port"   => 80,
                "path"   => "/batchelor/ws"
        );

        // 
        // Adjust from global values:
        // 
        if (isset($_SERVER['HTTPS'])) {
                $opts['scheme'] = "https";
        }
        if (isset($_SERVER['SERVER_ADDR'])) {
                $opts['addr'] = $_SERVER['SERVER_ADDR'];
        }
        if (isset($_SERVER['SERVER_PORT'])) {
                $opts['port'] = $_SERVER['SERVER_PORT'];
        }
        if (isset($_SERVER['SCRIPT_NAME'])) {
                $parts = explode("/", $_SERVER['SCRIPT_NAME']);
                if (strlen($parts[0]) == 0) {
                        array_shift($parts);
                }
                for ($part = array_pop($parts); isset($part) && $part != "ws"; $part = array_pop($parts)) {
                        
                }
                array_push($parts, "ws");
                $opts['path'] = implode("/", $parts);
        }

        if ($resolve) {
                $opts['addr'] = gethostbyaddr($opts['addr']);
        }
        if (strncmp($opts['addr'], "::", 2) == 0) {
                $opts['addr'] = "[" . $opts['addr'] . "]";      // IPv6 address need brackets
        }

        $root = sprintf("%s://%s:%d/%s", $opts['scheme'], $opts['addr'], $opts['port'], $opts['path']);
        return $root;
}

// 
// Returns the method entry based on its logical name (short).
// 
function ws_get_rpc_method_by_name($name)
{
        $names = array(
                "info", "func", "docs", "errors", "errmsg",
                "suspend", "resume",
                "enqueue", "dequeue", "queue", "watch",
                "opendir", "readdir", "fopen", "stat"
        );

        for ($i = 0; $i < count($names); $i++) {
                if ($names[$i] == $name) {
                        return ws_get_rpc_method_by_index($i);
                }
        }
        return null;
}

// 
// Returns the method entry based on its logical name (long).
// 
function ws_get_rpc_method_by_long($name)
{
        $entries = ws_get_rpc_method_by_index();
        for ($i = 0; $i < count($entries); $i++) {
                if ($entries[$i]['long'] == $name) {
                        return $entries[$i];
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
                        "func"   => null,
                        "name"   => "info",
                        "long"   => "batchelor.info",
                        "desc"   => "List all methods thats part of the API. This method returns an array of strings.",
                        "params" => null,
                        "return" => "array=[string]",
                        "script" => "api.php"
                ),
                array(
                        "func"   => null,
                        "name"   => "func",
                        "long"   => "batchelor.func",
                        "desc"   => "Show detailed information about a single RPC method. This method returns an object (with mixed values) describing the method.",
                        "params" => array(
                                "name" => "string"
                        ),
                        "return" => "object=()",
                        "script" => "api.php"
                ),
                array(
                        "func"   => null,
                        "name"   => "docs",
                        "long"   => "batchelor.docs",
                        "desc"   => "Show the HTTP RPC method API as manual. This method can only be used with HTML format.",
                        "params" => null,
                        "return" => null,
                        "script" => "api.php"
                ),
                array(
                        "func"   => null,
                        "name"   => "errors",
                        "long"   => "batchelor.errors",
                        "desc"   => "Return an collection of all errors defined (codes and messages).",
                        "params" => null,
                        "return" => "array=[object=(integer,string)]",
                        "script" => "error.php"
                ),
                array(
                        "func"   => null,
                        "name"   => "errmsg",
                        "long"   => "batchelor.errmsg",
                        "desc"   => "Return error message string for an given error code.",
                        "params" => array(
                                "code" => "integer"
                        ),
                        "return" => "string",
                        "script" => "error.php"
                ),
                array(
                        "func"   => "ws_suspend",
                        "name"   => "suspend",
                        "long"   => "batchelor.suspend",
                        "desc"   => "Suspend an already running job. Returns true on success and false on failure.",
                        "params" => array(
                                "result" => "string",
                                "jobid"  => "string"
                        ),
                        "return" => "boolean",
                        "script" => "suspend.php"
                ),
                array(
                        "func"   => "ws_resume",
                        "name"   => "resume",
                        "long"   => "batchelor.resume",
                        "desc"   => "Resume a job thats in paused or stopped state. Returns true on success and false on failure.",
                        "params" => array(
                                "result" => "string",
                                "jobid"  => "string"
                        ),
                        "return" => "boolean",
                        "script" => "resume.php"
                ),
                array(
                        "func"   => "ws_enqueue",
                        "name"   => "enqueue",
                        "long"   => "batchelor.enqueue",
                        "desc"   => "Enqueue (start) new job(s). Returns a list of job objects for the newly started jobs. The indata parameter is optional. If its missing, then its assumed that job data where uploaded thru HTTP POST (multipart/form-data). The result may contain multiple jobs.",
                        "params" => array(
                                "indata" => "string"
                        ),
                        "return" => "array=[object=(jobid=integer,date=string,time=string,stamp=integer)]",
                        "script" => "enqueue.php"
                ),
                array(
                        "func"   => "ws_dequeue",
                        "name"   => "dequeue",
                        "long"   => "batchelor.dequeue",
                        "desc"   => "Delete an running or finished job. Returns true on success and false on failure.",
                        "params" => array(
                                "result" => "string",
                                "jobid"  => "string"
                        ),
                        "return" => "boolean",
                        "script" => "dequeue.php"
                ),
                array(
                        "func"   => "ws_queue",
                        "name"   => "queue",
                        "long"   => "batchelor.queue",
                        "desc"   => "List pending, running and finished jobs. Returns an array of objects describing the jobs and their state. The sort and filer parameter is optional. Possible parameter values are the same as used in the queue viewer (in the web interface).",
                        "params" => array(
                                "sort"   => "string",
                                "filter" => "string"
                        ),
                        "return" => "array=[object=()]",
                        "script" => "queue.php"
                ),
                array(
                        "func"   => "ws_watch",
                        "name"   => "watch",
                        "long"   => "batchelor.watch",
                        "desc"   => "This method returns a list of jobs that has finished execute since last called by this method (the stamp parameter).",
                        "params" => array(
                                "stamp" => "integer"
                        ),
                        "return" => "array=[object=()]",
                        "script" => "watch.php"
                ),
                array(
                        "func"   => "ws_result",
                        "name"   => "opendir",
                        "long"   => "batchelor.opendir",
                        "desc"   => "List result directories. Returns an list of directory objects that can be used when calling the readdir method.",
                        "params" => null,
                        "return" => "array=[object=(result=string,jobid=string)]",
                        "script" => "result.php"
                ),
                array(
                        "func"   => "ws_result",
                        "name"   => "readdir",
                        "long"   => "batchelor.readdir",
                        "desc"   => "List files in a specific result directory. Returns an list of all files in the specific result directory pointed out by the method parameters (result and jobid). Also see the opendir and fopen method",
                        "params" => array(
                                "result" => "string",
                                "jobid"  => "string"
                        ),
                        "return" => "array=[string]",
                        "script" => "result.php"
                ),
                array(
                        "func"   => "ws_result",
                        "name"   => "fopen",
                        "long"   => "batchelor.fopen",
                        "desc"   => "Get content of an result file. The content is returned with MIME-type application/octet-stream (binary format). Call the opendir method to obtain values for the result and jobid parameters.",
                        "params" => array(
                                "result" => "string",
                                "jobid"  => "string",
                                "file"   => "string"
                        ),
                        "return" => WS_FOPEN_RETURN_FORMAT,
                        "script" => "result.php"
                ),
                array(
                        "func"   => "ws_stat",
                        "name"   => "stat",
                        "long"   => "batchelor.stat",
                        "desc"   => "This method lets the caller check the status of a single job. This could be useful for monitoring an enqueued job for completion (polling).",
                        "params" => array(
                                "result" => "string",
                                "jobid"  => "string"
                        ),
                        "return" => "object=()",
                        "script" => "stat.php"
                )
        );

        if ($index < 0) {
                return $functions;
        } else if (isset($functions[$index])) {
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
        if (!process_control($GLOBALS['hostid'], $result, $jobid, $signal, $error)) {
                switch ($error['where']) {
                        case "resdir":
                                if (!isset($error['reason'])) {
                                        put_error("The result directory don't exist");
                                } else {
                                        put_error("Unknown error related to result directory");
                                }
                                break;
                        case "pid":
                                if ($error['reason'] == "file") {
                                        put_error("The process identifier file is missing or invalid");
                                } else if ($error['reason'] == "perm") {
                                        put_error("Failed suspend process (permission problem)");
                                } else if ($error['reason'] == "proc") {
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
// Enqueue (starts) new job(s). If indata is null, then its assumed that
// indata comes from an uploaded file (set in $_FILES). The $jobs variable
// gets filled with data about the newly enqueued job(s).
// 
function ws_enqueue(&$indata, &$jobs)
{
        if (!enqueue_job($indata, $jobs)) {
                return false;
        }
        return true;
}

// 
// Dequeue (stop and removes) a job.
// 
function ws_dequeue($result, $jobid)
{
        if (!delete_single_job($GLOBALS['hostid'], $result, $jobid)) {
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
        if (is_array($jobs) && count($jobs) == 0) {
                return true;
        }
        if ($jobs == false) {
                return false;
        }
        return true;
}

// 
// List jobs that has finished execute since stamp. This function can be
// used to implement watch and monitor services.
// 
function ws_watch(&$out, $stamp)
{
        $jobs = get_jobs($GLOBALS['hostid']);
        if (is_array($jobs) && count($jobs) == 0) {
                return true;
        }
        if ($jobs == false) {
                return false;
        }
        foreach ($jobs as $result => $job) {
                if (isset($job['finished']) && $job['finished'] >= $stamp) {
                        $out[$result] = $job;
                }
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
        if (is_array($jobs) && count($jobs) == 0) {
                return true;
        }
        if ($jobs == false) {
                return false;
        }
        foreach ($jobs as $result => $job) {
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
        if ($handle) {
                while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != "..") {
                                $path = sprintf("%s/%s", $subdir, $file);
                                if (is_dir($path)) {
                                        if (!readdir_add_files($out, $path)) {
                                                return false;
                                        }
                                } else {
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
        if (!isset($result)) {
                put_error("Required argument result is unset");
                return false;
        }
        if (!isset($jobid)) {
                put_error("Required argument jobid is unset");
                return false;
        }

        $jobdir = sprintf("%s/jobs/%s/%s", CACHE_DIRECTORY, $GLOBALS['hostid'], $result);
        if (!file_exists($jobdir)) {
                put_error(sprintf("The job directory %s don't exists", $result));
                return false;
        }
        $cwd = getcwd();
        if ($cwd) {
                if (chdir($jobdir)) {
                        if (!readdir_add_files($out, ".")) {
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

// 
// Get path to an file in the job directory. The caller can then decide
// to either send its content or use it as a link for a downloader.
// 
function ws_fopen($result, $jobid, $file, &$out)
{
        if (!isset($result)) {
                put_error("Required argument result is unset");
                return false;
        }
        if (!isset($jobid)) {
                put_error("Required argument jobid is unset");
                return false;
        }
        if (!isset($file)) {
                put_error("Required argument file is unset");
                return false;
        }
        $path = sprintf("%s/jobs/%s/%s/%s", CACHE_DIRECTORY, $GLOBALS['hostid'], $result, $file);
        if (!file_exists($path)) {
                put_error("The requested file don't exists", $result);
                return false;
        }
        $out = $path;
        return true;
}

// 
// This method lets the caller stat an already enqueued job. Think of it as 
// an analogous of the UNIX stat(2) C-library function, but for web service.
// 
function ws_stat($result, $jobid, &$out)
{
        if (!isset($result)) {
                put_error("Required argument result is unset");
                return false;
        }
        if (!isset($jobid)) {
                put_error("Required argument jobid is unset");
                return false;
        }
        $jobs = get_jobs($GLOBALS['hostid']);
        if (is_array($jobs) && count($jobs) == 0) {
                put_error("The job queue is empty");
                return false;  // stating an non-existing job is an error.
        }
        if ($jobs == false) {
                return false;
        }
        foreach ($jobs as $res => $job) {
                if ($res == $result && $job['jobid'] == $jobid) {
                        $out = $job;
                        return true;
                }
        }
        put_error("Failed stat non-existing job");
        return false;
}

// 
// The error handler for the HTTP web service.
// 
function ws_http_error_handler($code, $error)
{
        $head = null;

        switch ($code) {
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

        if (!has_errors()) {
                put_error("Unknown error (no error message set)");
        }
        if (defined("WS_HTTP_SEND_ERROR_BODY") && WS_HTTP_SEND_ERROR_BODY) {
                printf("<html><head><title>HTTP Error %d</title></head><body><h2>%s</h2><hr>%s</body></html>\n", $code, $head, get_last_error());
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
        if (isset($path)) {
                return "application/octet-stream";
        } else {
                if ($GLOBALS['format'] == "xml") {
                        return "text/xml";
                } else if ($GLOBALS['format'] == "foa") {
                        return "text/x-foa";
                } else if ($GLOBALS['format'] == "json") {
                        return "application/json";
                } else if ($GLOBALS['format'] == "html") {
                        return "text/html";
                } else if ($GLOBALS['format'] == "human") {
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
        if (!defined("WS_ENABLE_HTTP")) {
                put_error("This script is not configured (see conf/config.inc)");
                ws_http_error_handler(403, WS_ERROR_CONFIG_PROBLEM);
        }
        if (!WS_ENABLE_HTTP) {
                put_error("This script is disallowed by the configuration (see conf/config.inc)");
                ws_http_error_handler(403, WS_ERROR_CONFIG_PROBLEM);
        }

        // 
        // Check that output buffering exists.
        // 
        if (!function_exists("ob_start") || !function_exists("ob_end_flush")) {
                error_log("Missing support for output buffering using ob_start() and ob_end_flush()");
                put_error("No support for output buffering (see web server log)");
                ws_http_error_handler(500, WS_ERROR_MISSING_EXTENSION);
        }

        // 
        // Check required parameters.
        // 
        if (isset($params)) {
                foreach ($params as $param) {
                        if (!isset($_REQUEST[$param])) {
                                put_error(sprintf("The <u>%s</u> parameter is missing", $param));
                                ws_http_error_handler(400, WS_ERROR_MISSING_PARAMETER);
                        }
                }
        }

        // 
        // Handle hostid
        // 
        if (defined("WS_HTTP_HOSTID_SOURCE")) {
                $source = WS_HTTP_HOSTID_SOURCE;
        } else if (defined("WS_HOSTID_SOURCE")) {
                $source = WS_HOSTID_SOURCE;
        } else {
                put_error("No hostid source defined in configuration (see conf/config.inc)");
                ws_http_error_handler(500, WS_ERROR_CONFIG_PROBLEM);
        }

        switch ($source) {
                case "param":                           // request param
                        $GLOBALS['hostid'] = md5($_REQUEST['hostid']);
                        break;
                case "cookie":                          // use cookie
                        if (!isset($_COOKIES['hostid'])) {
                                put_error("The <u>hostid</u> cookie is unset");
                                ws_http_error_handler(400, WS_ERROR_MISSING_PARAMETER);
                        }
                        $GLOBALS['hostid'] = md5($_COOKIES['hostid']);
                        break;
                case "auto":                            // set cookie
                        update_hostid_cookie();
                        break;
        }
        if (!isset($GLOBALS['hostid'])) {
                put_error("The <u>hostid</u> variable is missing");
                ws_http_error_handler(400, WS_ERROR_MISSING_PARAMETER);
        }

        if (defined("WS_HTTP_OUTPUT_FORMAT")) {
                $GLOBALS['format'] = WS_HTTP_OUTPUT_FORMAT;
        }
        if (isset($_REQUEST['format'])) {
                if ($_REQUEST['format'] == "xml" ||
                    $_REQUEST['format'] == "foa" ||
                    $_REQUEST['format'] == "php") {
                        $GLOBALS['format'] = $_REQUEST['format'];
                } elseif ($_REQUEST['format'] == "json") {
                        if (!extension_loaded("json")) {
                                put_error("The json format can't be used due to missing extension");
                                ws_http_error_handler(500, WS_ERROR_MISSING_EXTENSION);
                        }
                        $GLOBALS['format'] = "json";
                } elseif ($_REQUEST['format'] == "wddx") {
                        if (!extension_loaded("wddx")) {
                                put_error("The wddx format can't be used due to missing extension");
                                ws_http_error_handler(500, WS_ERROR_MISSING_EXTENSION);
                        }
                        $GLOBALS['format'] = "wddx";
                } elseif ($_REQUEST['format'] == "plain" ||
                    $_REQUEST['format'] == "human" ||
                    $_REQUEST['format'] == "text") {
                        $GLOBALS['format'] = "human";
                } elseif ($_REQUEST['format'] == "html") {
                        $GLOBALS['format'] = "html";
                }
        }
        if (!isset($GLOBALS['format'])) {
                $GLOBALS['format'] = "foa";
        }
}

//
// This function should be called from all XML-RPC web service scripts
// to setup the script for handling XML-RPC method requests.
// 
function ws_xmlrpc_session_setup($hostid = null)
{
        // 
        // Make sure this script is allowed to be runned:
        // 
        if (!defined("WS_ENABLE_XMLRPC")) {
                put_error("This script is not configured (see conf/config.inc)");
                ws_http_error_handler(403, WS_ERROR_CONFIG_PROBLEM);
        }
        if (!WS_ENABLE_XMLRPC) {
                put_error("This script is disallowed by the configuration (see conf/config.inc)");
                ws_http_error_handler(403, WS_ERROR_CONFIG_PROBLEM);
        }

        // 
        // Handle hostid
        // 
        if (defined("WS_XMLRPC_HOSTID_SOURCE")) {
                $source = WS_XMLRPC_HOSTID_SOURCE;
        } else if (defined("WS_HOSTID_SOURCE")) {
                $source = WS_HOSTID_SOURCE;
        } else {
                put_error("No hostid source defined in configuration (see conf/config.inc)");
                ws_http_error_handler(500, WS_ERROR_CONFIG_PROBLEM);
        }

        switch ($source) {
                case "param":                           // request param
                        $GLOBALS['hostid'] = md5($hostid);
                        break;
                case "cookie":                          // use cookie
                        if (!isset($_COOKIES['hostid'])) {
                                put_error("The hostid cookie is unset");
                                ws_http_error_handler(400, WS_ERROR_MISSING_PARAMETER);
                        }
                        $GLOBALS['hostid'] = md5($_COOKIES['hostid']);
                        break;
                case "auto":                            // set cookie
                        update_hostid_cookie();
                        break;
        }
        if (!isset($GLOBALS['hostid'])) {
                put_error("The hostid variable is missing");
                ws_http_error_handler(400, WS_ERROR_MISSING_PARAMETER);
        }
}

// 
// Setup session for handling REST web service requests. The fact that REST is
// based on HTTP lets us use cookies here.
// 
function ws_rest_session_setup($hostid = null)
{
        // 
        // The first thing to do is to set the output format:
        // 
        if (!isset($_REQUEST['encode'])) {
                $GLOBALS['format'] = "xml";
        } elseif ($_REQUEST['encode'] == "xml" ||
            $_REQUEST['encode'] == "foa") {
                $GLOBALS['format'] = $_REQUEST['encode'];
                unset($_REQUEST['encode']);
        } else {
                $GLOBALS['format'] = null;
        }

        // 
        // Include the callback functions:
        // 
        switch ($GLOBALS['format']) {
                case "xml":
                        include "include/rest/xml.inc";
                        break;
                case "foa":
                        include "include/rest/foa.inc";
                        break;
                default:
                        $GLOBALS['format'] = "xml";
                        include "include/rest/xml.inc";
                        send_error(WS_ERROR_INVALID_REQUEST, "invalid encode parameter value", true);
        }

        // 
        // Make sure this script is allowed to be runned:
        // 
        if (!defined("WS_ENABLE_REST")) {
                put_error("This script is not configured (see conf/config.inc)");
                send_error(WS_ERROR_CONFIG_PROBLEM, null, true);
        }
        if (!WS_ENABLE_REST) {
                put_error("This script is disallowed by the configuration (see conf/config.inc)");
                send_error(WS_ERROR_CONFIG_PROBLEM, null, true);
        }

        // 
        // Handle hostid
        // 
        if (defined("WS_REST_HOSTID_SOURCE")) {
                $source = WS_REST_HOSTID_SOURCE;
        } else if (defined("WS_HOSTID_SOURCE")) {
                $source = WS_HOSTID_SOURCE;
        } else {
                put_error("No hostid source defined in configuration (see conf/config.inc)");
                send_error(WS_ERROR_CONFIG_PROBLEM, null, true);
        }

        switch ($source) {
                case "param":                           // request param
                        $GLOBALS['hostid'] = md5($hostid);
                        break;
                case "cookie":                          // use cookie
                        if (!isset($_COOKIES['hostid'])) {
                                put_error("The hostid cookie is unset");
                                send_error(WS_ERROR_MISSING_PARAMETER, null, true);
                        }
                        $GLOBALS['hostid'] = md5($_COOKIES['hostid']);
                        break;
                case "auto":                            // set cookie
                        update_hostid_cookie();
                        break;
        }
        if (!isset($GLOBALS['hostid'])) {
                put_error("The hostid variable is missing");
                send_error(WS_ERROR_MISSING_PARAMETER, null, true);
        }

        // 
        // Refuse to continue unless register globals is disabled:
        // 
        if (ini_get("register_globals") != 0) {
                put_error("The PHP setting 'register_globals' is enabled (see php.ini), please disable it. Allowing register globals with the HTTP PUT action of the REST service could open a sequrity hole. The script will exit now.");
                send_error(WS_ERROR_CONFIG_PROBLEM, null, true);
        }
}

// 
// Setup session for handling SOAP web service requests. Since our
// SOAP service is only available using the HTTP, its safe to 
// enable use of cookies.
// 
function ws_soap_session_setup($hostid = null)
{
        // 
        // Make sure this script is allowed to be runned:
        // 
        if (!defined("WS_ENABLE_SOAP")) {
                put_error("This script is not configured (see conf/config.inc)");
                send_error(WS_ERROR_CONFIG_PROBLEM);
        }
        if (!WS_ENABLE_SOAP) {
                put_error("This script is disallowed by the configuration (see conf/config.inc)");
                send_error(WS_ERROR_CONFIG_PROBLEM);
        }

        // 
        // Handle hostid
        // 
        if (defined("WS_SOAP_HOSTID_SOURCE")) {
                $source = WS_SOAP_HOSTID_SOURCE;
        } else if (defined("WS_HOSTID_SOURCE")) {
                $source = WS_HOSTID_SOURCE;
        } else {
                put_error("No hostid source defined in configuration (see conf/config.inc)");
                send_error(WS_ERROR_CONFIG_PROBLEM);
        }

        switch ($source) {
                case "param":                           // request param
                        $GLOBALS['hostid'] = md5($hostid);
                        break;
                case "cookie":                          // use cookie
                        if (!isset($_COOKIES['hostid'])) {
                                put_error("The hostid cookie is unset");
                                send_error(WS_ERROR_MISSING_PARAMETER);
                        }
                        $GLOBALS['hostid'] = md5($_COOKIES['hostid']);
                        break;
                case "auto":                            // set cookie
                        update_hostid_cookie();
                        break;
        }
        if (!isset($GLOBALS['hostid'])) {
                put_error("The hostid variable is missing");
                send_error(WS_ERROR_MISSING_PARAMETER);
        }
}

// 
// Output the status of all known web service interfaces.
// 
function ws_print_services_status($relative = "")
{
        $service = array(
                "rest"   => array(
                        "status"    => WS_ENABLE_REST,
                        "name"      => "REST",
                        "extension" => null,
                        "link"      => "${relative}ws/docs/rest.php"
                ),
                "soap"   => array(
                        "status"    => WS_ENABLE_SOAP,
                        "name"      => "SOAP",
                        "extension" => "soap",
                        "link"      => "${relative}ws/docs/soap.php"
                ),
                "http"   => array(
                        "status"    => WS_ENABLE_HTTP,
                        "name"      => "HTTP RPC",
                        "extension" => null,
                        "link"      => "${relative}ws/docs/http.php"
                ),
                "xmlrpc" => array(
                        "status"    => WS_ENABLE_XMLRPC,
                        "name"      => "XML-RPC",
                        "extension" => null,
                        "link"      => "${relative}ws/docs/xmlrpc.php"
                )
        );
        echo "<p>\n";
        foreach ($service as $name => $data) {
                if (file_exists(sprintf("ws/%s", $name))) {
                        $status = WS_STATUS_UNKNOWN;
                        if (isset($data['status'])) {
                                if ($data['status']) {
                                        if (isset($data['extension']) && !extension_loaded($data['extension'])) {
                                                $status = WS_STATUS_MISSING_EXTENSION;
                                        } else {
                                                $status = WS_STATUS_ENABLED;
                                        }
                                } else {
                                        $status = WS_STATUS_DISABLED;
                                }
                        } else {
                                $status = WS_STATUS_NOT_CONFIGURED;
                        }

                        if ($status == WS_STATUS_ENABLED) {
                                printf("<img src=\"${relative}icons/nuvola/enabled.png\"><a href=\"%s\"> %s</a> is enabled.<br>\n", $data['link'], $data['name']);
                        } elseif ($status == WS_STATUS_DISABLED) {
                                printf("<img src=\"${relative}icons/nuvola/disabled.png\"><a href=\"%s\"> %s</a> is disabled by config.<br>\n", $data['link'], $data['name']);
                        } elseif ($status == WS_STATUS_MISSING_EXTENSION) {
                                printf("<img src=\"${relative}icons/nuvola/disabled2.png\"><a href=\"%s\"> %s</a> is disabled by missing %s extension.<br>\n", $data['link'], $data['name'], $data['extension']);
                        } elseif ($status == WS_STATUS_NOT_CONFIGURED) {
                                printf("<img src=\"${relative}icons/nuvola/unconfigured.png\"><a href=\"%s\"> %s</a> is not configured.<br>\n", $data['link'], $data['name']);
                        } else {
                                printf("<img src=\"${relative}icons/nuvola/unconfigured.png\"><a href=\"%s\"> %s</a> has unknown status.<br>\n", $data['link'], $data['name']);
                        }
                }
        }
        echo "</p>\n";
}
