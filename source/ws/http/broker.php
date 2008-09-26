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
// is the request broker that act as a bridge between logical names and physical
// scripts.
// 
// This script should be setup in the web server configuration to get called
// from URL's like http://localhost/batchelor/ws/http/name/?args
// 

ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . "../../../");

include "include/ws.inc";

// 
// Find out how we was called. The last component of SCRIPT_NAME should
// be the logical name that we used to map against the real script.
// 
$GLOBALS['name'] = basename($_SERVER['SCRIPT_NAME']);

foreach(ws_get_rpc_method_by_index() as $entry) {
    if($entry['name'] == $name) {
	$GLOBALS['script'] = $entry['script'];
	unset($entry);
	break;
    }
}

// 
// Include script if found.
// 
if(isset($GLOBALS['script'])) {
    include $GLOBALS['script'];
    exit(0);
}

// 
// If we got here then the logical name didn't match any script.
// 
put_error("Failed map logical name to script");
ws_http_error_handler(404, WS_ERROR_REQUEST_BROKER);

?>
