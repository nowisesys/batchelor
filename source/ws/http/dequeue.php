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
// implements the RPC method dequeue.
// 

ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . "../../../" . PATH_SEPARATOR . "../");

//
// Get configuration.
// 
include_once "conf/config.inc";

include_once "include/common.inc";
include_once "include/ws.inc";
include "include/queue.inc";
include "include/delete.inc";

// 
// Setup HTTP web service session. This will terminate the script if any 
// problem is detected.
// 
ws_http_session_setup(array( "result", "jobid" ));

// 
// Send result in XML format.
// 
function send_result_xml($result)
{
    print "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    printf("<boolean>%s</boolean>\n", $result ? "true" : "false");
}

// 
// Send result in FOA format.
// 
function send_result_foa($result)
{
    printf("%s\n", $result ? "true" : "false");
}

// 
// Send result in PHP format.
// 
function send_result_php($result)
{
    printf("%s", serialize($result));
}

// 
// Send result in JSON format.
// 
function send_result_json($result)
{
    printf("%s", json_encode($result));
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
     case "php":
	send_result_php($result);
	break;
     case "json":
	send_result_json($result);
	break;
     default:
	put_error(sprintf("Method dequeue don't implements format %s", $GLOBALS['format']));
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
if(!ws_dequeue($_REQUEST['result'], $_REQUEST['jobid'])) {
    send_result(false);
    put_error("Failed call ws_dequeue()");
    ws_http_error_handler(409, WS_ERROR_FAILED_CALL_METHOD);
}
send_result(true);

// 
// Send response.
// 
header(sprintf("Content-Type: %s; charset=%s", ws_get_mime_type(), "UTF-8"));
header("Connection: close");

ob_end_flush();

?>
