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
    printf("  <tns:error>\n");
    printf("    <tns:code>%d</tns:code>\n", $code);
    printf("    <tns:message>%s</tns:message>\n", isset($message) ? $message : get_error($code));
    printf("  </tns:error>\n");
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
    printf("<tns:result state=\"%s\" type=\"%s\"\n", $state, $type);
    printf("   xmlns:tns=\"%s\"\n", WS_REST_SCHEMA_URL);
    printf("   xmlns:xlink=\"http://www.w3.org/1999/xlink\">\n"); 
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
    printf("  <tns:link xlink:href=\"%s\" $actions />\n", $href);
}

// 
// Send status message in reponse to an modifying action.
// 
function send_status($message)
{
    printf("  <tns:status>$message</tns:status>\n");
}

// 
// Send a single job object.
// 
function send_job(&$job, $request)
{
    printf("  <tns:job timezone=\"%s\">\n", ini_get("date.timezone"));
    foreach($job as $key => $val) {
	if($key == "name") {
	    printf("    <%s>%s</%s>\n", $key, utf8_encode($val), $key);
	} else {
	    printf("    <%s>%s</%s>\n", $key, $val, $key);
	}
    }
    print "  </tns:job>\n";
}

// 
// Send a single file using requested encoding.
// 
function send_file($filename) 
{
    if(WS_FOPEN_RETURN_FORMAT == "base64") {
	printf("<tns:file encoding=\"base64\">%s</tns:file>\n", 
	       base64_encode(file_get_contents($filename)));
    } else {
	printf("<tns:file encoding=\"binary\">%s</tns:file>\n", 
	       file_get_contents($filename));
    }
}

// 
// Send a generic key/value message.
// 
function send_data($key, $val) 
{
    printf("  <tns:%s>%s</tns:%s>\n", $key, $val, $key);
}

?>
