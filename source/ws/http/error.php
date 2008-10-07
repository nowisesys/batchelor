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
// This script is part of the lightweight HTTP web service interface. It provides
// information about error codes. Either it returns a collection of all error 
// codes/messages or if called with an error code, it returns the associated error
// message.
// 

ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . "../../../" . PATH_SEPARATOR . "../");

//
// Get configuration.
// 
include_once "conf/config.inc";

include_once "include/common.inc";
include_once "include/ws.inc";
include "include/queue.inc";

// 
// Setup HTTP web service session. This will terminate the script if any 
// problem is detected.
// 
if(basename($_SERVER['PHP_SELF']) == "error.php") {
    if(isset($_REQUEST['code'])) {
	ws_http_session_setup(array( "code" ));
    } else {
	ws_http_session_setup();
    }
} else {
    $name = basename($_SERVER['PHP_SELF']);
    if(isset($_REQUEST['code']) && $name == "errmsg") {
	ws_http_session_setup(array( "code" ));
    } else if($name == "errors") {
	ws_http_session_setup();
    } else {
	put_error("Missmatch between parameters and method/script name.");
	ws_http_error_handler(400, WS_ERROR_INVALID_REQUEST);
    }
}

// 
// Send collection in XML format.
// 
function send_collection_xml()
{
    print "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    print "<errors>\n";
    $errors = get_error();
    for($i = 1; $i <= count($errors); $i++) {
	printf("  <error>\n");
	printf("    <code>%d</code>\n", $i);
	printf("    <message>%s</message>\n", $errors[$i - 1]);
	printf("  </error>\n");
    }
    print "</errors>\n";
}

// 
// Send collection in FOA format.
// 
function send_collection_foa()
{
    $errors = get_error();
    print "[\n";
    for($i = 1; $i <= count($errors); $i++) {
	printf("(\n%d\n%s\n)\n", $i, $errors[$i - 1]);
    }
    print "]\n";
}

// 
// Send error message in XML format.
// 
function send_error_message_xml($code)
{
    print "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    printf("<error>%s</error>\n", get_error($code));
}

// 
// Send error message in FOA format.
// 
function send_error_message_foa($code)
{
    printf("%s\n", get_error($code));
}

// 
// Start output buffering.
// 
ob_start();

if(isset($_REQUEST['code'])) {
    switch($GLOBALS['format']) {
     case "xml":
	send_error_message_xml($_REQUEST['code']);
	break;
     case "foa":
     	send_error_message_foa($_REQUEST['code']);
     	break;
     default:
	put_error(sprintf("Method %s don't implements format %s", $GLOBALS['name'], $GLOBALS['format']));
	ws_http_error_handler(400, WS_ERROR_INVALID_FORMAT);
    }
} else {
    switch($GLOBALS['format']) {
     case "xml":
	send_collection_xml();
	break;
     case "foa":
     	send_collection_foa();
     	break;
     default:
	put_error(sprintf("Method %s don't implements format %s", $GLOBALS['name'], $GLOBALS['format']));
	ws_http_error_handler(400, WS_ERROR_INVALID_FORMAT);
    }
}

// 
// Send response.
// 
header(sprintf("Content-Type: %s; charset=%s", ws_get_mime_type(), "UTF-8"));
header("Connection: close");

ob_end_flush();

?>
