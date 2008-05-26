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
// tools who need access to files inside the job directory.
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
    
    if(FILES_PHP_SEND_ERROR_BODY) {
	printf("<html><head><title>HTTP Error %d</title></head><body><h2>%s</h2><hr>%s</body></html>\n", 
	       $code, $head, $hint);
    }
    exit(1);
}

// 
// Make sure this script is allowed to be runned:
// 
if(defined("FILES_PHP_ENABLED") && !FILES_PHP_ENABLED) {
    error_handler(403, "This script is disallowed by the configuration (see conf/config.inc)");
}

// 
// Check required parameters.
// 
$required = array("jobid", "file");
if(!FILES_PHP_USE_HOSTID_COOKIE && !FILES_PHP_SET_HOSTID_COOKIE) {
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
if(FILES_PHP_USE_HOSTID_COOKIE) {
    $GLOBALS['hostid'] = $_COOKIES['hostid'];      // use cookie
} else if(FILES_PHP_SET_HOSTID_COOKIE) {
    include "../include/common.inc";               // set cookie
    update_hostid_cookie();
} else {
    $GLOBALS['hostid'] = $_REQUEST['hostid'];      // request param
}

// 
// Build path to job directory and file:
// 
$jobdir  = sprintf("%s/jobs/%s/%s", CACHE_DIRECTORY, $GLOBALS['hostid'], $_REQUEST['jobid']);
if(FILES_PHP_USE_BASE_SUBDIR) {
    $jobfile = sprintf("%s/%s/%s", $jobdir, FILES_PHP_USE_BASE_SUBDIR, $_REQUEST['file']);
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
readfile($jobfile);

?>
