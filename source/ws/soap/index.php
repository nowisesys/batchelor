<?php

// -------------------------------------------------------------------------------
//  Copyright (C) 2007-2008 Anders L�vgren
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
// This is the server side code implementing a SOAP service.
// 

// 
// Because the WSDL generated by Axis is packing input and output into
// complex types (objects), we need to follow this convention. We have
// (at least) two ways to do that. Either we use associative arrays with 
// keys matching the names as returned by $client->__getTypes() or we
// need to define classes with members having thoose names. The following
// example shows two possible implementations for the version method.
// 
// // 
// // Example: Using array as response type
// // 
// function version() {
//   return array("versionReturn" => "1.0");
// }
// 
// // 
// // Example: Using class as response type
// // 
// class VersionResponse {
//   var $versionReturn;
//   function VersionResponse() {
//     $this->versionReturn = WS_SOAP_INTERFACE_VERSION;
//   }
// }
// function version() {
//   return new VersionResponse("1.0");
// }
// 
// Even though using an array would be a simple solution, it's not our
// prefered way of doing this. The main objection against it is that it 
// doesn't preserve the object oriented approach.
// 
// All request/response classes can be found in include/soap.inc for 
// thoose who like to write their own clients.
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
include "include/soap.inc";

ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache 

class Batchelor {
    function enqueue($obj)
    {
    }
    
    function queue($obj)
    {
	$jobs = array();
	if(strlen($obj->in0) == 0) {
	    $obj->in0 = "none";
	}
	if(strlen($obj->in1) == 0) {
	    $obj->in1 = "all";
	}
	if(ws_queue($jobs, $obj->in0, $obj->in1)) {
	    error_log(sprintf("sort: %s", serialize($obj)));
	    error_log(sprintf("jobs: %s", serialize($jobs)));
	    return new QueueResponse($jobs);
	}
    }
    
    function resume($obj)
    {
    }
    
    // JobIdent
    function suspend($obj)
    {
    }
    
    function version()
    {
	error_log("version called");
	return array("versionReturn" => WS_SOAP_INTERFACE_VERSION);
	// return new VersionResponse();
    }
    
    // JobIdent
    function dequeue($obj)
    {
    }
    
    function watch($stamp)
    {
	error_log(sprintf("stamp: %s", serialize($stamp)));
	error_log("watch called: stamp=$stamp");
    }
    
    // function opendir()
    // {
    // }
    
    // function readdir($obj)
    // {
    // }
    
    // function fopen($obj, $file)
    // {
    // }
}

// 
// This function gets called to report error.
// 
function send_error($code)
{
    throw new SoapFault($code, get_error($code));
}

//
// Setup the SOAP session.
// 
ws_soap_session_setup();

// $server = new SoapServer("../wsdl/batchelor.wsdl", array('soap_version' => SOAP_1_2));
$server = new SoapServer("../wsdl/batchelor.wsdl");
$server->setClass("Batchelor");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $server->handle();
} else {
    echo "This SOAP server can handle following functions:<br />";
    $functions = $server->getFunctions();
    foreach($functions as $func) {
	echo $func . "<br />\n";
    }
}

?>
