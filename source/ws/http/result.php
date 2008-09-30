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
// This script is part of the lightweight HTTP web service interface. This script
// implements the RPC method result.
// 

ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . "../../../" . PATH_SEPARATOR . "../");

include "include/common.inc";
include "include/queue.inc";
include_once "include/ws.inc";
include "include/delete.inc";

//
// Get configuration.
// 
include "conf/config.inc";

// 
// Setup HTTP web service session. This will terminate the script if any 
// problem is detected.
// 
ws_http_session_setup();

//
// Send directory list as XML.
// 
function print_opendir_xml(&$data)
{
    print "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    print "<dirs>\n";
    foreach($data as $result => $jobid) {
	print "  <dir>\n";
	printf("    <result>%s</result>\n", $result);
	printf("    <jobid>%s</jobid>\n", $jobid);
	print "  </dir>\n";
    }
    print "</dirs>\n";
}

//
// Send directory list in FOA format.
// 
function print_opendir_foa(&$data)
{
    print "[\n";
    foreach($data as $result => $jobid) {
	print "(\n";
	printf("%s\n", $result);
	printf("%s\n", $jobid);
	print ")\n";
    }
    print "]\n";
}

// 
// Process opendir request.
// 
function process_opendir()
{
    $data = null;
    if(!ws_opendir($data)) {
	put_error("Failed call ws_opendir()");
	ws_http_error_handler(409, WS_ERROR_FAILED_CALL_METHOD);
    }
    switch($GLOBALS['format']) {
     case "xml":
	print_opendir_xml($data);
	break;
     case "foa":
     	print_opendir_foa($data);
     	break;
     default:
	put_error(sprintf("Method opendir don't implements format %s", $GLOBALS['format']));
	ws_http_error_handler(400, WS_ERROR_INVALID_FORMAT);
    }
}

//
// Send files list as XML.
// 
function print_readdir_xml(&$data)
{
    print "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    print "<files>\n";
    for($i = 0; $i < count($data); $i++) {
	printf("  <file>%s</file>\n", $data[$i]);
    }
    print "</files>\n";
}

//
// Send files list in FOA format.
// 
function print_readdir_foa(&$data)
{
    print "[\n";
    for($i = 0; $i < count($data); $i++) {
	printf("%s\n", $data[$i]);
    }
    print "]\n";
}

// 
// Process readdir request.
// 
function process_readdir()
{
    foreach(array( "result", "jobid") as $param) {
	if(!isset($_REQUEST[$param])) {
	    put_error("Required parameter $param is unset");
	    ws_http_error_handler(400, WS_ERROR_MISSING_PARAMETER);
	}
    }
    $data = null;
    if(!ws_readdir($_REQUEST['result'], $_REQUEST['jobid'], $data)) {
	put_error("Failed call ws_readdir()");
	ws_http_error_handler(409, WS_ERROR_FAILED_CALL_METHOD);
    }  
    switch($GLOBALS['format']) {
     case "xml":
	print_readdir_xml($data);
	break;
     case "foa":
     	print_readdir_foa($data);
     	break;
     default:
	put_error(sprintf("Method opendir don't implements format %s", $GLOBALS['format']));
	ws_http_error_handler(400, WS_ERROR_INVALID_FORMAT);
    }
}

// 
// Process fopen request. This method ignore (by its nature) the format parameter
// because we always send the result as application/octet-stream.
// 
function process_fopen()
{
    foreach(array( "result", "jobid", "file") as $param) {
	if(!isset($_REQUEST[$param])) {
	    put_error("Required parameter $param is unset");
	    ws_http_error_handler(400, WS_ERROR_MISSING_PARAMETER);
	}
    }
    $data = null;
    if(!ws_fopen($_REQUEST['result'], $_REQUEST['jobid'], $_REQUEST['file'], $data)) {
	put_error("Failed call ws_fopen()");
	ws_http_error_handler(409, WS_ERROR_FAILED_CALL_METHOD);
    }

    header("Content-Type: application/octet-stream");
    header("Connection: close");

    ob_end_flush();    
    readfile($data);
    
    exit(0);   // Stop further script execution.
}

// 
// Start output buffering.
// 
ob_start();

// 
// This script is multifunction.
// 
if($GLOBALS['name'] == "opendir") {
    process_opendir();
} else if($GLOBALS['name'] == "readdir") {
    process_readdir();
} else if($GLOBALS['name'] == "fopen") {
    process_fopen();
} else {
    put_error(sprintf("Unexpected RPC method %s", $GLOBALS['name']));
    ws_http_error_handler(500, WS_ERROR_UNEXPECTED_METHOD);
}

// 
// Send response.
// 
header(sprintf("Content-Type: %s; charset=%s", ws_get_mime_type(), "UTF-8"));
header("Connection: close");

ob_end_flush();

?>
