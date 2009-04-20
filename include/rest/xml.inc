<?php

// -------------------------------------------------------------------------------
//  Copyright (C) 2009 Anders Lövgren
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
// This file implements the callback functions used by the REST web
// service to provide end user with XML encoded responses.
// 

// 
// Define the XML-schema (URL and target namespace):
// 
define("WS_REST_URL_SCHEMA", sprintf("%s/schema/rest", get_server_url()));
define("WS_REST_TNS_SCHEMA", "http://it.bmc.uu.se/batchelor/rest/200901");
define("WS_REST_TNS_XLINK",  "http://it.bmc.uu.se/batchelor/xlink/200901");

// 
// The error handler for the REST web service. The error parameter gets send
// to the client (peer). If any error are set by put_error(), then they are
// flushed to the web server log.
// 
function send_error($code, $message, $headers = false, $loop = false) 
{ 
    log_errors(true);
    if($headers) {
	header("Content-Type: text/xml");
	header("Connection: close");
	printf("<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n");
    }
    if(!$loop) {
	send_start_tag("failed", "error");
    }
    printf("  <error>\n");
    printf("    <code>%d</code>\n", $code);
    printf("    <message>%s</message>\n", isset($message) ? $message : get_error($code));
    printf("  </error>\n");
    if(!$loop) {
	send_end_tag();
	exit(1);
    }
}

// 
// Send header.
// 
function send_start_tag($state, $type, $array = false) 
{
    printf("<tns:result xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'\n");
    printf("   xmlns:tns='%s'\n", WS_REST_TNS_SCHEMA);
    printf("   xmlns:xlink='%s'\n", WS_REST_TNS_XLINK);
    printf("   xsi:schemaLocation='%s %s'\n", WS_REST_TNS_SCHEMA, WS_REST_URL_SCHEMA);
    printf("   tns:state=\"%s\" tns:type=\"%s\">\n", $state, $type);
}

// 
// Send footer.
// 
function send_end_tag($array = false) 
{
    print "</tns:result>\n";
}

// 
// Send resource link. The attr argument contains an array of supported
// actions for this link, and the expected object returned by taking this
// action (like get => object). If attr is a string, then its interpret
// as the get action.
// 
function send_link($href, $attr = null)
{
    $actions = "";
    if(isset($attr)) {
	if(is_array($attr)) {
	    foreach($attr as $action => $object) {
		$actions .= "tns:$action=\"$object\" ";
	    }
	} elseif(is_string($attr)) {
	    $actions = "tns:get=\"$attr\"";
	}
    }
    printf("  <link xlink:href=\"%s\" $actions />\n", $href);
}

// 
// Send status message in reponse to an modifying action.
// 
function send_status($message)
{
    printf("  <status>$message</status>\n");
}

// 
// Send a single job object.
// 
function send_job(&$job, $request)
{
    printf("  <job timezone=\"%s\">\n", ini_get("date.timezone"));
    foreach($job as $key => $val) {
	if($key == "name") {
	    printf("    <%s>%s</%s>\n", $key, utf8_encode($val), $key);
	} else {
	    printf("    <%s>%s</%s>\n", $key, $val, $key);
	}
    }
    print "  </job>\n";
}

// 
// Send a single file using requested encoding.
// 
function send_file($filename) 
{
    if(WS_FOPEN_RETURN_FORMAT == "base64") {
	$content = base64_encode(file_get_contents($filename));
	printf("<file tns:encoding=\"base64\" name=\"%s\" size=\"%d\">\n", 
	       basename($filename), strlen($content));
	printf("  <content>%s</content>\n", $content);
	printf("</file>\n"); 	
    } else {
	$name = basename($filename);
	$size = filesize($filename);
	printf("<file tns:encoding=\"binary\" name=\"%s\" size=\"%s\">\n",
	       $name, $size);
	printf("  <content>");
	readfile($filename);
	printf("  </content>\n");
	printf("</file>\n"); 
    }
}

// 
// Send version.
// 
function send_version($version) 
{
    printf("  <version>%s</version>\n", $version);
}

?>
