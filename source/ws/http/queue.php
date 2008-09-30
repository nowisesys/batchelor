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
// This script is part of the lightweight HTTP web service interface. This script
// implements the RPC method queue.
// 

ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . "../../../" . PATH_SEPARATOR . "../");

include_once "include/common.inc";
include_once "include/ws.inc";
include "include/queue.inc";
include "include/delete.inc";

//
// Get configuration.
// 
include "conf/config.inc";

// 
// Fill optional request values with defaults.
// 
if(!isset($_REQUEST['sort'])) {
    $_REQUEST['sort'] = "none";
}
if(!isset($_REQUEST['filter'])) {
    $_REQUEST['filter'] = "all";
}
// foreach(array( "sort", "filter" ) as $key) {
//     if(!isset($_REQUEST[$key])) {
// 	$_REQUEST[$key] = null;
//     }
// }

// 
// Setup HTTP web service session. This will terminate the script if any 
// problem is detected.
// 
ws_http_session_setup();

// 
// Send result in XML format.
// 
function send_result_xml(&$jobs)
{
    print "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    print "<jobs>\n";
    foreach($jobs as $result => $job) {
	print "  <job>\n";
	printf("    <result>%s</result>\n", $result);
	foreach($job as $key => $val) {
	    printf("    <%s>%s</%s>\n", $key, $val, $key);
	}
	print "  </job>\n";
    }
    print "</jobs>\n";
}

// 
// Send result in FOA format.
// 
function send_result_foa(&$jobs)
{
    print "[\n";
    foreach($jobs as $result => $job) {
	printf("(\nresult=%s\n", $result);
	foreach($job as $key => $val) {
	    printf("%s=%s\n", $key, $val);
	}
	print ")\n";
    }    
    print "]\n";
}

// 
// Send result to client.
// 
function send_result(&$jobs)
{
    switch($GLOBALS['format']) {
     case "xml":
	send_result_xml($jobs);
	break;
     case "foa":
     	send_result_foa($jobs);
     	break;
     default:
	put_error(sprintf("Method queue don't implements format %s", $GLOBALS['format']));
	ws_http_error_handler(400, WS_ERROR_INVALID_FORMAT);
    }
}

// 
// Start output buffering.
// 
ob_start();

// 
// Call requested method.
// 
$jobs = array();
if(!ws_queue($jobs, $_REQUEST['sort'], $_REQUEST['filter'])) {
    ws_http_error_handler(409, WS_ERROR_FAILED_CALL_METHOD);
}
// print_r($jobs);
send_result($jobs);

// 
// Send response.
// 
header(sprintf("Content-Type: %s; charset=%s", ws_get_mime_type(), "UTF-8"));
header("Connection: close");

ob_end_flush();

?>
