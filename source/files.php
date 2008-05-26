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
// Script for downloading files from the job directory. This script 
// is *not* expected to be called from a browser, but from third party 
// tools who need to access files inside the job directory.
// 
// Files are accessed as:
// 
//   http://localhost/batchelor/files.php?file=started&jobid=1211547173
// 
// A third parameter (hostid) might be required depending on configuration,
// see the FILES_PHP_XXX options in conf/config.inc. Using cookies might
// confuse some programs, so the config allows it to be disabled as the 
// hostid source.
// 
// The following HTTP error codes are explicit used:
// 
//   400 - Wrong or missing parameters.
//   403 - Access is forbidden. The reason might be that the requested file is
//         outside job directory or the script is disabled by configuration.
//   404 - The file do not exist.
//   500 - Programming errors.
// 
// The HTTP version of stat(2) are to use HEAD instead of GET:
// 
//   HEAD /batchelor/files.php?file=stdout&jobid=1211547173 HTTP/1.0
// 

//
// Get configuration.
// 
include "../conf/config.inc";

// 
// The error handler. 
// 
function error_handler($code, $hint)
{    
    $head = null;
    
    switch($code) {
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
    
    header($head);
    header("Connection: close");
    
    if(defined("FILES_PHP_SEND_ERROR_BODY") && FILES_PHP_SEND_ERROR_BODY) {
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
function get_mime_type($path)
{
    return "application/octet-stream";
}

// 
// Make sure this script is allowed to be runned:
// 
if(!defined("FILES_PHP_ENABLED")) {
    error_handler(403, "This script is not configured (see conf/config.inc)");
}
if(!FILES_PHP_ENABLED) {
    error_handler(403, "This script is disallowed by the configuration (see conf/config.inc)");
}

// 
// Check required parameters.
// 
$required = array("jobid", "file");
if(FILES_PHP_HOSTID_SOURCE == "param") {
    array_push($required, "hostid");
}
foreach($required as $param) {
    if(!isset($_REQUEST[$param])) {
	error_handler(400, sprintf("The <u>%s</u> parameter is missing", $param));
    }
}

// 
// Handle hostid
// 
switch(FILES_PHP_HOSTID_SOURCE) {
 case "param":                           // request param
    $GLOBALS['hostid'] = $_REQUEST['hostid'];      
    break;
 case "cookie":                          // use cookie
    if(!isset($_COOKIES['hostid'])) {
	error_handler(400, "The <u>hostid</u> cookie is unset");
    }
    $GLOBALS['hostid'] = $_COOKIES['hostid'];
    break;
 case "auto":                            // set cookie
    include "../include/common.inc";     
    update_hostid_cookie();
    break;
}
if(!isset($GLOBALS['hostid'])) {
    error_handler(400, "The <u>hostid</u> variable is missing");
}

// 
// Build path to job directory and file:
// 
$jobdir = sprintf("%s/jobs/%s/%s", CACHE_DIRECTORY, $GLOBALS['hostid'], $_REQUEST['jobid']);
if(FILES_PHP_BASE_DIRECTORY) {
    $jobfile = sprintf("%s/%s/%s", $jobdir, FILES_PHP_BASE_DIRECTORY, $_REQUEST['file']);
} else {
    $jobfile = sprintf("%s/%s", $jobdir, $_REQUEST['file']);
}

// 
// Make sure the file is inside the job directory:
// 
if((strpos(dirname(realpath($jobfile)), $jobdir)) === FALSE) {
    error_handler(403, sprintf("The requested file must be located inside the job directory %s", $_REQUEST['jobid']));
}

// 
// Send error message if job directory or file is missing.
// 
if(!file_exists($jobdir)) {
    error_handler(404, sprintf("The job directory %s don't exist", $_REQUEST['jobid']));
}
if(!file_exists($jobfile)) {
    error_handler(404, sprintf("The requested file %s don't exist", $_REQUEST['file']));
}

// 
// Send file to client.
// 
if(FILES_PHP_SEND_FILE_PROPS) {
    header(sprintf("Content-Type: %s", get_mime_type($jobfile)));
    header(sprintf("Content-Length: %d", filesize($jobfile)));
}
readfile($jobfile);

?>
