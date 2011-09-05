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
// service to provide end user with FOA encoded responses.
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
	header(sprintf("Content-Type: %s", ws_get_mime_type()));
	header("Connection: close");
    }
    if(!$loop) {
	send_start_tag("failed", "error", false);
    }
    printf("  error = (\n");
    printf("    code = %d\n", $code);
    printf("    message = %s\n", isset($message) ? $message : get_error($code));
    printf("  )\n");
    if(!$loop) {
	send_end_tag(false);
	exit(1);
    }
}

// 
// Send header.
// 
function send_start_tag($state, $type, $array = true) 
{
    printf("result = (\n");
    printf("  state = %s\n", $state);
    printf("  type = %s\n", $type);
    if($array) {
	print "  data = [\n";
    }
}

// 
// Send footer.
// 
function send_end_tag($array = true) 
{
    if($array) {
	print "  ]\n";
    }
    print ")\n";
}

// 
// Send resource link. The attr argument contains an array of supported
// actions for this link, and the expected object returned by taking this
// action (like get => object). If attr is a string, then its interpret
// as the get action.
// 
function send_link($href, $attr = null)
{
    printf("  link = (\n");
    printf("    href = %s\n", $href);
    if(isset($attr)) {
	if(is_array($attr)) {
	    foreach($attr as $action => $object) {
		print "    $action = $object\n";
	    }
	} elseif(is_string($attr)) {
	    print "    get = $attr\n";
	}
    }
    printf("  )\n");
}

// 
// Send status message in reponse to an modifying action.
// 
function send_status($message)
{
    printf("  status = %s\n", $message);
}

// 
// Send a single job object.
// 
function send_job(&$job, $request)
{
    printf("  job = (\n");
    printf("    timezone = %s\n", date('T'));
    printf("    data = (\n");
    foreach($job as $key => $val) {
	if($key == "name") {
	    printf("      %s = %s\n", $key, utf8_encode($val));
	} else {
	    printf("      %s = %s\n", $key, $val);
	}
    }
    printf("    )\n");
    printf("  )\n");
}

// 
// Send a single file using requested encoding. See the specification at
// http://it.bmc.uu.se/andlov/proj/libfoa/spec.php for an explanation of
// this funky piece of code.
// 
function send_file($filename) 
{
    if(WS_FOPEN_RETURN_FORMAT == "base64") {
	$buff = base64_encode(file_get_contents($filename));
	$size = strlen($buff);
	printf("  @file = (\n");
	printf("    name = %s\n", basename($filename));
	printf("    size = %d\n", $size);
	printf("    encoding = base64\n");
	printf("  )\n");
	printf($buff);
    } else {
	printf("  @file = (\n");
	printf("    name = %s\n", basename($filename));
	printf("    size = %d\n", filesize($filename));
	printf("    encoding = binary\n");
	printf("  )\n");
	readfile($filename);
    }
}

// 
// Send version.
// 
function send_version($version) 
{
    printf("  version = %s\n", $version);
}

?>
