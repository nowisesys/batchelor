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
// This script serves WSDL (port definitions) and XSD (type definitions) 
// to SOAP service consumers (clients).
// 

ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . "../../../../");

// 
// Include support functions for web services:
// 
include "include/ws.inc";

// 
// Send WSDL document. The generation of the WSDL document can be disabled by
// creating a pre-parsed WSDL named batchelor.wsdl.cache, in that case it is
// sent "as is" without any substitutions.
// 
function send_port_definitions() 
{
    if(file_exists("batchelor.wsdl.cache")) {
	readfile("batchelor.wsdl.cache");
    } else {
	$root = get_server_url();
	$addr = array( 
		       "@soapaddr@" => "$root/soap/",
		       "@wsdladdr@" => "$root/schema/wsdl/" 
		       );
    
	$fs = fopen("batchelor.wsdl", "r");
	if($fs) {
	    while($str = fgets($fs)) {
		printf("%s\n", str_replace(array_keys($addr), array_values($addr), $str));
	    }
	    fclose($fs);
	}
    }
}

// 
// Send XSD document.
// 
function send_type_definitions() 
{
    readfile("batchelor.xsd");
}

// 
// This function gets called to handle an invalid request.
// 
function send_service_usage() 
{
    $addr = get_server_url();
    $wsdl = sprintf("%s/schema/wsdl/?wsdl", $addr);
    
    print "<html><head><title>Batchelor Web Service (SOAP)</title>\n";
    print "<body>\n";
    print "<h1>Batchelor Web Service (SOAP)</h1>\n";
    print "<p>WSDL for the SOAP service: <a href=\"${wsdl}\">${wsdl}</a></p>\n";
    print "</body>\n";
    print "</html>\n";
}

if(isset($_REQUEST['wsdl'])) {
    send_port_definitions();
} elseif(isset($_REQUEST['xsd'])) {
    send_type_definitions();
} else {
    send_service_usage();
}

?>
