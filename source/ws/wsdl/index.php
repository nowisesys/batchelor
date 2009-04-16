<?php

// -------------------------------------------------------------------------------
//  Copyright (C) 2007-2009 Anders L�vgren
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

// 
// Get server URL.
// 
function get_server_url() 
{
    // 
    // Default options:
    // 
    $opts = array( 
		   "scheme" => "http",
		   "addr" => "localhost",
		   "port" => 80,
		   "path" => "/batchelor/ws" 
		   );
    
    // 
    // Adjust from global values:
    // 
    if(isset($_SERVER['HTTPS'])) {
	$opts['scheme'] = "https";
    }
    if(isset($_SERVER['SERVER_ADDR'])) {
	$opts['addr'] = $_SERVER['SERVER_ADDR'];
    }
    if(isset($_SERVER['SERVER_PORT'])) {
	$opts['port'] = $_SERVER['SERVER_PORT'];
    }
    if(isset($_SERVER['SCRIPT_NAME'])) {
	$opts['path'] = dirname(dirname($_SERVER['SCRIPT_NAME']));
    }
    if($opts['path'][0] == '/') {
	$opts['path'] = substr($opts['path'], 1);
    }
	
    $root = sprintf("%s://%s:%d/%s", $opts['scheme'], $opts['addr'], $opts['port'], $opts['path']);
    return $root;
}

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
		       "@wsdladdr@" => "$root/wsdl/" 
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
    $wsdl = sprintf("%s/wsdl/?wsdl", $addr);
    
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