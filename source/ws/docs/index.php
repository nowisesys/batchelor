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
// Shows information on the various web service interfaces that can
// be used (if enabled).
// 

ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . "../../.." . PATH_SEPARATOR . "..");

//
// Get configuration.
// 
include "conf/config.inc";
include "include/ui.inc";

if(!defined("WS_ENABLE_HTTP")) {
    define ("WS_ENABLE_HTTP", false);
}
if(!defined("WS_ENABLE_XMLRPC")) {
    define ("WS_ENABLE_XMLRPC", false);
}
if(!defined("WS_ENABLE_REST")) {
    define ("WS_ENABLE_REST", false);
}
if(!defined("WS_ENABLE_SOAP")) {
    define ("WS_ENABLE_SOAP",   true);
}

function print_title() 
{
    printf("%s - Web Services", HTML_PAGE_TITLE);
}

function print_body()
{
    $status = array(
		    "http"   => array( "status" => WS_ENABLE_HTTP,
				       "name"   => "HTTP RPC",
				       "link"   => "http.php" ),
		    "xmlrpc" => array( "status" => WS_ENABLE_XMLRPC,
				       "name"   => "XML-RPC",
				       "link"   => "xmlrpc.php" ),
		    "soap"   => array( "status" => WS_ENABLE_SOAP,
				       "name"   => "SOAP",
				       "link"   => "soap.php" ),
		    "rest"   => array( "status" => WS_ENABLE_REST,
				       "name"   => "REST",
				       "link"   => "rest.php" )
		);
    
    printf("<h2><img src=\"../../icons/nuvola/info.png\"> %s - Web Services</h2>\n", HTML_PAGE_TITLE);    
    echo "<span id=\"secthead\">Status:</span>\n";
    echo "<p>\n";    
    foreach($status as $name => $data) {
	if(file_exists(sprintf("ws/%s", $name))) {
	    if($data['status']) {
		printf("<img src=\"../../icons/nuvola/enabled.png\"><a href=\"%s\"> %s</a> is enabled.<br>\n", 
		       $data['link'], $data['name']);
	    } else {
		printf("<img src=\"../../icons/nuvola/disabled.png\"><a href=\"%s\"> %s</a> is disabled.<br>\n", 
		       $data['link'], $data['name']);
	    }
	}
    }
    echo "</p>\n";
    echo "<p>See <a href=\"intro.php\">introduction</a> for getting started information.</p>\n";
}
 
function print_html($what)
{
    switch($what) {
     case "body":
	print_body();
	break;
     case "title":
	print_title();
	break;
     default:
	print_common_html($what);    // Use default callback.
	break;
    }
}

chdir("../..");
load_ui_template("apidoc");

?>
