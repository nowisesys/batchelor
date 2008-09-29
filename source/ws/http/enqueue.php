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
// implements the RPC method enqueue.
// 

ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . "../../../" . PATH_SEPARATOR . "../");

include "include/common.inc";
include "include/queue.inc";
include_once "include/ws.inc";

//
// Get configuration.
// 
include "conf/config.inc";

// 
// Setup HTTP web service session. This will terminate the script if any 
// problem is detected. This script accepts only one optional parameter
// named indata (missing if indata is sent thru file upload), so we bypass 
// any checks in ws_http_session_setup() and leave the error handling to
// enqueue_job() called by ws_enqueue().
// 
ws_http_session_setup();

// 
// Make sure indata has been set.
// 
if(!isset($_REQUEST['indata'])) {
    $_REQUEST['indata'] = null;
}

// 
// Send result in XML format.
// 
function send_result_xml($job)
{
    print "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    print "<job>\n";
    foreach($job as $key => $val) {
	printf("  <%s>%s</%s>\n", $key, $val, $key);
    }
    print "</job>\n";
}

// 
// Send result in FOA format.
// 
function send_result_foa($job)
{
    $res = array_values($job);
    print "(\n";    
    for($i = 0; $i < count($res); $i++) {
	printf("%s\n", $res[$i]);
    }
    print ")\n";
}

// 
// Send result to client.
// 
function send_result($result)
{
    switch($GLOBALS['format']) {
     case "xml":
	send_result_xml($result);
	break;
     case "foa":
     	send_result_foa($result);
     	break;
     default:
	put_error(sprintf("Method enqueue don't implements format %s", $GLOBALS['format']));
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
$job = null;
if(!ws_enqueue($_REQUEST['indata'], $job)) {
    ws_http_error_handler(409, WS_ERROR_FAILED_CALL_METHOD);
}
send_result($job);

// 
// Send response.
// 
header(sprintf("Content-Type: %s; charset=%s", ws_get_mime_type(), "UTF-8"));
header("Connection: close");

ob_end_flush();

?>
