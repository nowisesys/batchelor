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
// implements the RPC-function info().
// 

ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . "../../../");

include "include/common.inc";
include "include/queue.inc";
include_once "include/ws.inc";

//
// Get configuration.
// 
include "conf/config.inc";

// 
// Setup HTTP web service session. This will terminate the script if any 
// problem is detected.
// 
ws_http_session_setup();

if($GLOBALS['name'] == "info") {
    $entries = ws_get_rpc_method();
    $methods = array();
    foreach($entries as $entry) {
	array_push($methods, $entry['name']);
    }
    $content = sprintf("These methods exists:\n-----------------------\n%s", 
		       implode(", ", $methods));    
} else if($GLOBALS['name'] == "func") {
    $entry = ws_get_rpc_method(WS_RPC_METHOD_FUNC);
} else {
    ws_http_error_handler(500, sprintf("Unexpected RPC method %s", $GLOBALS['name']));
}
    
header(sprintf("Content-Type: %s; charset=%s", "text/plain", "ISO-8859-1"));
header(sprintf("Content-Length: %d", strlen($content)));
header("Connection: close");

print $content;

?>
