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
// This is the XML-RPC web service implementing the UserLand Sotware's specification:
// http://www.xmlrpc.com/spec
//

ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . "../../../");

//
// Get configuration.
// 
include "conf/config.inc";

include "include/common.inc";
include "include/queue.inc";
include "include/ws.inc";
include "include/delete.inc";

// 
// Initilize the XML-RPC session.
// 
ws_xmlrpc_session_setup();

// 
// Decode the XML request from HTTP POST. The request is received as 
// an XML message from stdin (php://input).
// 
function decode_request()
{
    if(!extension_loaded("domxml") && !extension_loaded("dom")) {
	send_error(WS_ERROR_MISSING_EXTENSION, "Failed parse request");
	return false;
    }
    
    // 
    // Get XML document from stdin:
    // 
    $xmldata = @file_get_contents('php://input');
    $request = array();
    
    // 
    // Parse XML request:
    // 
    if(extension_loaded("domxml")) {	
	$errors = array();
	$xmlopt = DOMXML_LOAD_PARSING |
                  DOMXML_LOAD_COMPLETE_ATTRS |
	          DOMXML_LOAD_SUBSTITUTE_ENTITIES |
	          DOMXML_LOAD_DONT_KEEP_BLANKS;
	$xmldoc = @domxml_open_mem($xmldata, $xmlopt, $errors);
	if(!$xmldoc) {
	    send_error(WS_ERROR_INVALID_FORMAT, "Failed open XML");
	    return false;
	}
	$parent = $xmldoc->document_element();
	$childs = $parent->child_nodes();
	foreach($childs as $child) {
	    if($child->node_type() == XML_ELEMENT_NODE) {
		if($child->tagname == "methodName") {
		    $request['method'] = $child->get_content();
		} elseif($child->tagname == "params") {
		    $params = $child->child_nodes();
		    if(count($params)) {
			$request['params'] = array();
			foreach($params as $param) {
			    if($param->node_type() == XML_ELEMENT_NODE) {
				$value = $param->first_child()->get_content();
				$type  = $param->first_child()->first_child()->tagname;
				array_push($request['params'], array( $type => $value ));
			    }
			}
		    }
		} else {
		    send_error(WS_ERROR_INVALID_FORMAT, sprintf("Unexpected tag %s", $child->tagname));
		    return false;
		}
	    }
	}
	$xmldoc->free();
    } elseif(extension_loaded("dom")) {
	$request = array();
	$xmldoc = new DOMDocument();
	if(!$xmldoc->loadXML($xmldata)) {
	    send_error(WS_ERROR_INVALID_FORMAT, "Failed open XML");
	    return false;
	}
        foreach($xmldoc->documentElement->childNodes as $node) {	
	    if($node->nodeType == XML_ELEMENT_NODE) {
		if($node->nodeName == "methodName") {
		    $request['method'] = $node->nodeValue;
		} elseif($node->nodeName == "params") {
		    $params = $node->childNodes;
		    if(count($params)) {
			$request['params'] = array();
			foreach($params as $param) {
			    if($param->nodeType == XML_ELEMENT_NODE) {
				$type  = $param->firstChild->nodeName;
				$value = $param->firstChild->firstChild->nodeValue;
				array_push($request['params'], array( $type => $value ));
			    }
			}
		    }
		} else {
		    send_error(WS_ERROR_INVALID_FORMAT, sprintf("Unexpected tag %s", $node->nodeName));
		    return false;
		}
	    }
	}
    }
    return $request;	
}

// 
// Validate parameters.
// 
function check_params(&$request, &$entry)
{
    if(isset($entry['params'])) {
	if(!isset($request['params'])) {
	    send_error(WS_ERROR_MISSING_PARAMETER, sprintf("This method requires %d parameters", count($entry['params'])), true);
	    return false;
	}
	if(count($request['params']) < count($entry['params'])) {
	    send_error(WS_ERROR_MISSING_PARAMETER, sprintf("Expected %d parameters, but got %d.", 
							   count($entry['params']), 
							   count($request['params'])),
							   true);
	    return false;
	}
	if(count($request['params']) > count($entry['params'])) {
	    send_error(WS_ERROR_INVALID_REQUEST, "Too many parameters.", true);
	    return false;
	} 
    } else {
	if(isset($request['params'])) {
	    send_error(WS_ERROR_INVALID_REQUEST, "Method don't accept any parameters.", true);
	    return false;
	}
    }
    return true;
}

// 
// Return request parameter value.
// 
function get_request_param(&$request, $index)
{
    foreach(array( "string", "int", "boolean", "double" ) as $type) {
	if(isset($request['params'][$index][$type])) {
	    return $request['params'][$index][$type];
	}
    }
    return null;
}
  
// 
// Send queue object as response.
// 
function send_queue_response(&$jobs)
{
    print "      <array>\n";
    print "        <data>\n";
    if(isset($jobs)) {
	foreach($jobs as $result => $job) {
	    print "          <struct>\n";
	    print "            <member>\n";
	    print "              <name>result</name>\n";
	    print "              <value><int>$result</int></value>\n";
	    print "            </member>\n";
	    foreach($job as $key => $val) {
		$type = "string";
		if(is_bool($val)) {
		    $type = "boolean";
		} elseif(is_float($val)) {
		    $type = "double";
		} elseif(is_numeric($val)) {
		    $type = "int";
		}
		print "            <member>\n";
		printf("              <name>%s</name>\n", $key);
		printf("              <value><%s>%s</%s></value>\n", $type, $val, $type);
		print "            </member>\n";
	    }
	    print "          </struct>\n";
	}
    }
    print "        </data>\n";
    print "      </array>\n";
}

// 
// Send result from job directory listing.
// 
function send_opendir_response(&$dirs)
{
    print "      <array>\n";
    print "        <data>\n";
    foreach($dirs as $result => $jobid) {
	print "          <struct>\n";
	print "            <member>\n";
	print "              <name>result</name>\n";
	print "              <value><string>$result</string></value>\n";
	print "            </member>\n";
	print "            <member>\n";
	print "              <name>jobid</name>\n";
	print "              <value><string>$jobid</string></value>\n";
	print "            </member>\n";
	print "          </struct>\n";
    }
    print "        </data>\n";
    print "      </array>\n";
}

// 
// Send result from a files in job directory listing.
// 
function send_readdir_response(&$files)
{
    print "      <array>\n";
    print "        <data>\n";
    foreach($files as $file) {
	printf("          <value><string>%s</string></value>\n", $file);
    }
    print "        </data>\n";
    print "      </array>\n";
}

// 
// Send result from a files in job directory listing.
// 
function send_fopen_response($file)
{
    if(WS_FOPEN_RETURN_FORMAT == "base64") {
	print "      <base64>";
	if(file_exists($file)) {
	    printf("%s", base64_encode(file_get_contents($file)));
	}
	print "</base64>\n";
    } else {
	print "      <string>";
	if(file_exists($file)) {
	    readfile($file);
	}
	print "</string>\n";
    }
}

function send_stat_response($result, $job) 
{
    print "      <struct>\n";
    print "        <member>\n";
    print "          <name>result</name>\n";
    print "          <value><int>$result</int></value>\n";
    print "        </member>\n";
    foreach($job as $key => $val) {
	$type = "string";
	if(is_bool($val)) {
	    $type = "boolean";
	} elseif(is_float($val)) {
	    $type = "double";
	} elseif(is_numeric($val)) {
	    $type = "int";
	}
	print "        <member>\n";
	printf("          <name>%s</name>\n", $key);
	printf("          <value><%s>%s</%s></value>\n", $type, $val, $type);
	print "        </member>\n";
    }
    print "      </struct>\n";
}

// 
// Send true or false response.
// 
function send_boolean_response($value)
{
    printf("      <boolean>%d</boolean>\n", $value ? 1 : 0);
}

// 
// Send result from enqueue job method.
// 
function send_enqueue_response(&$jobs)
{
    print "      <array>\n";
    print "        <data>\n";
    foreach($jobs as $job) {
	print "          <struct>\n";
	foreach($job as $key => $val) {
	    $type = "string";
	    if(is_bool($val)) {
		$type = "boolean";
	    } elseif(is_float($val)) {
		$type = "double";
	    } elseif(is_numeric($val)) {
		$type = "int";
	    }
	    print "            <member>\n";
	    printf("              <name>%s</name>\n", $key);
	    printf("              <value><%s>%s</%s></value>\n", $type, $val, $type);
	    print "            </member>\n";
	}
	print "          </struct>\n";
    }
    print "        <data>\n";
    print "      <array>\n";
}

// 
// Send all error messages defined.
// 
function send_errors_response($errors = null)
{
    print "      <array>\n";
    print "        <data>\n";
    if(!isset($errors)) {
	$errors = get_error();
    }
    foreach($errors as $error) {
	print "          <value><string>$error</string></value>\n";
    }
    print "        </data>\n";
    print "      </array>\n";
    return true;
}

// 
// Send error message for requested error.
// 
function send_error_descr($error)
{
    printf("      <string>%s</string>\n", get_error($error));
    return true;
}

// 
// Send list of methods.
// 
function send_methods_list($entries = null)
{
    print "      <array>\n";
    print "        <data>\n";
    if(!isset($entries)) {
	$entries = ws_get_rpc_method_by_index();
    }
    foreach($entries as $entry) {
	printf("          <value><string>%s</string></value>\n", $entry['long']);
    }
    print "        </data>\n";
    print "      </array>\n";
    return true;
}

// 
// Send description of method.
// 
function send_method_descr($name)
{
    $entry = ws_get_rpc_method_by_long($name);
    // 
    // Method description:
    // 
    print "    <param>\n";
    print "      <name>info</name>\n";
    print "      <value>\n";
    print "        <struct>\n";
    print "          <member>\n";
    print "            <name>name</name>\n";
    printf("            <value><string>%s</string></value>\n", $entry['long']);
    print "          </member>\n";
    print "          <member>\n";
    print "            <name>desc</name>\n";
    printf("            <value><string>%s</string></value>\n", $entry['desc']);
    print "          </member>\n";
    print "        </struct>\n";
    print "      </value>\n";
    print "    </param>\n";
    // 
    // Parameters:
    // 
    if(isset($entry['params'])) {
	print "    <param>\n";
	print "      <name>params</name>\n";
	print "      <value>\n";
	print "        <struct>\n";
	foreach($entry['params'] as $name => $type) {
	    print "          <member>\n";
	    print "            <name>$name</name>\n";
	    print "            <value><$type></$type></value>\n";
	    print "          </member>\n";
	}
	print "        </struct>\n";
	print "      </value>\n";
	print "    </param>\n";
    }
    // 
    // Return values:
    // 
    if(isset($entry['return'])) {
	print "    <param>\n";
	print "      <name>return</name>\n";
	print "      <value>\n";
	// 
	// Reuse response function to generate output.
	// 
	switch($entry['name']) {
	 case "info":
	    $entries = array(array("long" => "name"));
	    $status = send_methods_list($entries);
	    break;
	 case "func":
	    send_method_descr(null);
	    break;
	 case "docs":
	    break;
	 case "errors":
	    send_errors_response(array(""));
	    break;
	 case "errmsg":
	    send_error_descr(0);
	    break;
	 case "suspend":
	 case "resume":
	 case "dequeue":
	    send_boolean_response(true);
	    break;
	 case "enqueue":	    
	    $result = array( array("jobid" => "integer", "date" => "string", "time" => "string", "stamp" => "integer") );
	    send_enqueue_response($result);
	    break;
	 case "queue":
	 case "watch":
	    $result = array("" => array("jobid" => ""));
	    send_queue_response($result);
	    break;
	 case "opendir":
	    $result = array("" => "");
	    send_opendir_response($result);
	    break;
	 case "readdir":
	    $result = array("");
	    send_readdir_response($result);
	    break;
	 case "fopen":
	    send_fopen_response("");
	    break;
	 case "stat":
	    $result = array("jobid" => "");
	    send_stat_response("", $result);
	    break;
	}
	print "      </value>\n";
	print "    </param>\n";
    }
    return true;
}

// 
// Send start of result output.
// 
function send_params_start($full = true)
{
    print "  <params>\n";
    if($full) {
	print "    <param>\n";
    }
}

// 
// Send end of result output.
// 
function send_params_end($full = true)
{
    if($full) {
	print "    <param>\n";   
    }
    print "  </params>\n";
}

// 
// Call method and send response (might be error).
// 
function send_response($request)
{
    print "<?xml version=\"1.0\" encoding=\"UTF-8\">\n";
    print "<methodResponse>\n";
    $entry = ws_get_rpc_method_by_long($request['method']);
    
    $result = null;
    $status = false;
    
    switch($entry['name']) {
     case "info":
	send_params_start();
	$status = send_methods_list();
	send_params_end();
	break;
     case "func":
	if(check_params($request, $entry)) {
	    send_params_start(false);
	    $status = send_method_descr(get_request_param($request, 0));
	    send_params_end(false);
	} else {
	    $status = true;
	}
	break;
     case "docs":
	send_error(WS_ERROR_UNEXPECTED_METHOD, "This method can't be called from XML-RPC", true);
	$status = true;
	break;
     case "errors":
	if(check_params($request, $entry)) {
	    send_params_start();
	    $status = send_errors_response();
	    send_params_end();
	} else {
	    $status = true;
	}
	break;
     case "errmsg":
	if(check_params($request, $entry)) {
	    send_params_start();
	    $status = send_error_descr(get_request_param($request, 0));
	    send_params_end();
	} else {
	    $status = true;
	}
	break;
     case "suspend":
	if(check_params($request, $entry)) {
	    if(($status = ws_suspend(get_request_param($request, 0), 
				     get_request_param($request, 1)))) {
		send_params_start();
		send_boolean_response($status);
		send_params_end();
	    }
	} else {
	    $status = true;        // handled
	}
	break;
     case "resume":
	if(check_params($request, $entry)) {
	    if(($status = ws_resume(get_request_param($request, 0), 
				    get_request_param($request, 1)))) {
		send_params_start();
		send_boolean_response($status);
		send_params_end();
	    }
	} else {
	    $status = true;        // handled
	}
	break;
     case "enqueue":
	if(($status = ws_enqueue(get_request_param($request, 0), $result))) {
	    send_params_start();
	    send_enqueue_response($result);
	    send_params_end();
	} else {
	    $status = true;        // handled
	}
	break;
     case "dequeue":
	if(check_params($request, $entry)) {
	    if(($status = ws_dequeue(get_request_param($request, 0), 
				     get_request_param($request, 1)))) {
		send_params_start();
		send_boolean_response($status);
		send_params_end();
	    }
	} else {
	    $status = true;        // handled
	}
	break;
     case "queue":
	if(isset($request['params'])) {
	    if(isset($request['params'][0])) {
		if(isset($request['params'][1])) {
		    if(($status = ws_queue($result, 
					   get_request_param($request, 0), 
					   get_request_param($request, 1)))) {
			send_params_start();
			send_queue_response($result);
			send_params_end();
		    }
		} else {
		    if(($status = ws_queue($result, get_request_param($request, 0)))) {
			send_params_start();
			send_queue_response($result);
			send_params_end();
		    }
		}
	    }
	} else {
	    if(($status = ws_queue($result))) {
		send_params_start();
		send_queue_response($result);
		send_params_end();
	    }
	}
	break;
     case "watch":
	if(check_params($request, $entry)) {
	    if(($status = ws_watch($result, get_request_param($request, 0)))) {
		send_params_start();
		send_queue_response($result);
		send_params_end();
	    } 
	} else {
	    $status = true;        // handled
	}
	break;
     case "opendir":
	if(($status = ws_opendir($result))) {
	    send_params_start();
	    send_opendir_response($result);
	    send_params_end();
	}
	break;
     case "readdir":
	if(check_params($request, $entry)) {
	    if(($status = ws_readdir(get_request_param($request, 0), 
				     get_request_param($request, 1), 
				     $result))) {
		send_params_start();
		send_readdir_response($result);
		send_params_end();
	    }
	} else {
	    $status = true;        // handled
	}
	break;
     case "fopen":
	if(check_params($request, $entry)) {
	    if(($status = ws_fopen(get_request_param($request, 0), 
				   get_request_param($request, 1), 
				   get_request_param($request, 2), 
				   $result))) {
		send_params_start();
		send_fopen_response($result);
		send_params_end();
	    }
	} else {
	    $status = true;
	}
	break;
     case "stat":
	if(check_params($request, $entry)) {
	    if(($status = ws_stat(get_request_param($request, 0), 
				  get_request_param($request, 1), 
				  $result))) {
		send_params_start();
		send_stat_response(get_request_param($request, 0), $result);
		send_params_end();
	    }
	} else {
	    $status = true;        // handled
	}
	break;
     default:
	send_error(WS_ERROR_REQUEST_BROKER, "The method name is unknown or not implemented", true);
	$status = true;
	break;
    }
    if(!$status) {
	send_error(WS_ERROR_FAILED_CALL_METHOD, get_last_error(), true);
    }
    print "</methodResponse>\n";
}

// 
// Send error response.
// 
function send_error($code, $message, $embed = false)
{
    if(!$embed) {
	print "<?xml version=\"1.0\" encoding=\"UTF-8\">\n";
	print "<methodResponse>\n";
    }
    print "  <fault><value><struct>\n";
    print "    <member>\n";
    print "      <name>faultCode</name>\n";
    print "      <value><int>$code</int></value>\n";
    print "    </member>\n";
    print "    <member>\n";
    print "      <name>faultString</name>\n";
    print "      <value><string>$message</string></value>\n";
    print "    </member>\n";
    print "  </struct></value></fault>\n";
    if(!$embed) {
	print "</methodResponse>\n";
    }
}

// 
// Output is always XML:
// 
header("Content-Type: text/xml");
header("Connection: close");

// 
// Receive request:
// 
$request = decode_request();

// 
// Send response:
// 
send_response($request);

?>
