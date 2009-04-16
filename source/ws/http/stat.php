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
// This script is part of the lightweight HTTP web service interface. This script
// implements the RPC method queue.
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
ws_http_session_setup(array( "result", "jobid" ));

// 
// Send result in XML format.
// 
function send_result_xml($result, &$job)
{
    print "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    print "<job>\n";
    printf("  <result>%s</result>\n", $result);
    foreach($job as $key => $val) {
	if($key == "name") {
	    $val = htmlentities($val, ENT_QUOTES, "ISO-8859-1");
	}
	printf("  <%s>%s</%s>\n", $key, $val, $key);
    }
    print "</job>\n";
}

// 
// Send result in FOA format.
// 
function send_result_foa($result, &$job)
{
    printf("(\nresult=%s\n", $result);
    foreach($job as $key => $val) {
	if($key == "name") {
	    if(strpbrk($val, "[]()\n")) {
		$spec = array("[", "]", "(", ")", "\\");
		$repl = array("%5B", "%5D", "%28", "%29", "%5C");
		$val = str_replace($spec, $repl, $val);
	    }
	}
	printf("%s=%s\n", $key, $val);
    }
    print ")\n";
}

// 
// Send result in PHP format.
// 
function send_result_php($result, &$job)
{
    $arr = array();
    $job['result'] = $result;
    $arr[] = (object)$job;
    printf("%s", serialize($arr));
}

// 
// Send result in JSON format.
// 
function send_result_json($result, &$job)
{
    $arr = array();
    $job['result'] = $result;
    if(isset($job['name'])) {
	$job['name'] = utf8_encode($job['name']);
    }
    $arr[] = (object)$job;
    printf("%s", json_encode($arr));
}

// 
// Send result to client.
// 
function send_result($result, &$job)
{
    switch($GLOBALS['format']) {
     case "xml":
	send_result_xml($result, $job);
	break;
     case "foa":
     	send_result_foa($result, $job);
     	break;
     case "php":
     	send_result_php($result, $job);
     	break;
     case "json":
     	send_result_json($result, $job);
     	break;
     default:
	put_error(sprintf("Method queue don't implements format %s", $GLOBALS['format']));
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
$job = array();
if(!ws_stat($_REQUEST['result'], $_REQUEST['jobid'], $job)) {
    ws_http_error_handler(409, WS_ERROR_FAILED_CALL_METHOD);
}
send_result($_REQUEST['result'], $job);

// 
// Send response.
// 
header(sprintf("Content-Type: %s; charset=%s", ws_get_mime_type(), "UTF-8"));
header("Connection: close");

ob_end_flush();

?>