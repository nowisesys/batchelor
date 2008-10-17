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
// Client for testing the web services interface.
// 

//
// The script should only be run in CLI mode.
//
if(isset($_SERVER['SERVER_ADDR'])) {
    die("This script should be runned in CLI mode.\n");
}

include "../include/getopt.inc";

// 
// Call the HTTP RPC web service interface.
// 
function get_http_rpc_response($options) 
{
    if(!extension_loaded("curl")) {
	die("(-) error: the curl extension is not loaded\n");
    }

    $url = sprintf("%s/%s", $options->baseurl, $options->type);
    if(isset($options->func)) {
	$url .= sprintf("/%s", $options->func);
    }
    if(isset($options->params)) {
	$url .= sprintf("?%s", $options->params);
    }
    if(isset($options->format)) {
	if(isset($options->params)) {
	    $url .= sprintf("&format=%s", $options->format);
	} else {
	    $url .= sprintf("?format=%s", $options->format);
	}
    }
    if($options->debug) {
	printf("debug: using url %s\n", $url);
    }
    
    $curl = curl_init();	
    if($curl) {
	if($options->verbose) {
	    if($options->debug) {
		curl_setopt($curl, CURLOPT_VERBOSE, 1);
	    }
	    print "(i) info: curl initilized\n";
	}
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_HEADER, 1);
	if($options->file) {
	    // 
	    // Simulate file form upload:
	    // 
	    $post = array( 
			   'file' => sprintf("@%s", $options->file)
			   );
	    curl_setopt($curl, CURLOPT_POST, 1);
	    curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
	}
	if($options->verbose) {
	    print "(i) info: calling remote method\n";
	}
	echo "\n";
	if(!curl_exec($curl)) {
	    echo "(-) error: failed connect to server\n";
	}
	curl_close($curl);
    } else {
	echo "(-) error: failed initilize curl\n";
    }
}

// 
// Call the XML-RPC web service interface.
// 
function get_xmlrpc_response($options) 
{
    if(!extension_loaded("curl")) {
	die("(-) error: the curl extension is not loaded\n");
    }
    
    $url = sprintf("%s/%s/", $options->baseurl, $options->type);
	
    // 
    // We need to split the parameters into its part and generate the 
    // XML payload for XML-RPC request.
    // 
    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    $xml .= "<methodCall>\n";
    $xml .= sprintf("  <methodName>%s</methodName>\n", $options->func);
    if(isset($options->params)) {
	$params = explode("&", $options->params);
	$xml .= "  <params>\n";
	foreach($params as $param) {
	    list($pk, $pv) = explode("=", $param);
	    if(is_numeric($pv)) {
		$xml .= sprintf("    <param><int>%d</int></param>\n", $pv);
	    } else if(is_string($pv)) {
		$xml .= sprintf("    <param><string>%s</string></param>\n", $pv);
	    }
	}
	$xml .= "  </params>\n";
    }
    $xml .= "</methodCall>\n";
    if($options->debug) {
	printf("debug: xml message to send:\n'%s'\n", $xml);
    }
    
    $curl = curl_init();	
    if($curl) {
	if($options->verbose) {
	    if($options->debug) {
		curl_setopt($curl, CURLOPT_VERBOSE, 1);
	    }
	    print "(i) info: curl initilized\n";
	}
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
	curl_setopt($curl, CURLOPT_HEADER, 1);
	if($options->verbose) {
	    print "(i) info: calling remote method\n";
	}
	echo "\n";
	if(!curl_exec($curl)) {
	    echo "(-) error: failed connect to server\n";
	}
	curl_close($curl);
    } else {
	echo "(-) error: failed initilize curl\n";
    }
}

// 
// Call the REST web service interface.
// 
function get_rest_response($options) 
{
    if(!extension_loaded("curl")) {
	die("(-) error: the curl extension is not loaded\n");
    }
    
    $url = sprintf("%s/%s/", $options->baseurl, $options->type);
    if(isset($options->func)) {
	$url .= sprintf("/%s", $options->func);
    }
    if(isset($options->params)) {
	$url .= sprintf("/%s", $options->params);
    }
    if($options->debug) {
	printf("debug: using url %s\n", $url);
    }
    
    $curl = curl_init();	
    if($curl) {
	if($options->verbose) {
	    if($options->debug) {
		curl_setopt($curl, CURLOPT_VERBOSE, 1);
	    }
	    print "(i) info: curl initilized\n";
	}
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_HEADER, 1);
	if($options->action == "post" ||
	   $options->action == "put") {
	    if(!isset($options->file)) {
		die(sprintf("(-) error: using --action=%s requires an --file option.\n",
			    $options->action));
	    }
	    if($options->action == "put") {
		curl_setopt($curl, CURLOPT_PUT, 1);
	    }
	    if($options->file) {
		// 
		// Simulate file form upload:
		// 
		$post = array( 
			       'file' => sprintf("@%s", $options->file)
			       );
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
	    }
	} elseif($options->action == "delete") {
	    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
	}
	if($options->verbose) {
	    print "(i) info: calling remote method\n";
	}
	echo "\n";
	if(!curl_exec($curl)) {
	    echo "(-) error: failed connect to server\n";
	}
	curl_close($curl);
    } else {
	echo "(-) error: failed initilize curl\n";
    }
}

// 
// Call web service interface HTTP RPC, XML-RPC or REST.
// 
function get_rpc_response($options)
{
    if($options->type == "http") {
	get_http_rpc_response($options);
    } elseif($options->type == "xmlrpc") {
	get_xmlrpc_response($options);
    } elseif($options->type == "rest") {
	get_rest_response($options);
    }
}

//
// Show basic usage.
//
function usage($prog, $defaults)
{    
    print "$prog - test utility for web services\n";
    print "\n";      
    print "Usage: $prog options...\n";
    print "Options:\n";
    printf("  --base=url:      The base URL to web services [%s]\n", $defaults->baseurl);
    printf("  --type=str:      The web service interface, either http, xmlrpc or rest [%s]\n", $defaults->type);
    printf("  --func=name:     Execute the named function (see --func=info)\n");
    printf("  --file=name:     Use file when posting data (see --post=file)\n");
    printf("  --params=str:    URL-encoded function parameters (e.g. result=1234&id=99)\n"); 
    printf("  --format=str:    Output format: either foa, xml, json, php (, html or human).\n");
    printf("  --action=str:    Request method: either get, post, put or delete.\n");
    printf("  -d,--debug:      Enable debug.\n");
    printf("  -v,--verbose:    Be more verbose.\n");
    printf("  -h,--help:       This help.\n");
    printf("  -V,--version:    Show version info.\n");
    printf("Convenience options:\n");
    printf("  --get:           Alias for --action=get\n");
    printf("  --post=file:     Alias for --action=post --file=name\n");
    printf("  --delete:        Alias for --action=delete\n");
    printf("  --put:           Alias for --action=put\n");
    print "\n";
    print "Example:  php ws.php --func=enqueue --type=xmlrpc --params='indata=test'\n";
}

//
// Show verison info.
//
function version($prog, $vers)
{
    print "$prog - web service test tool ($vers)\n";
}

// 
// Parse command line options.
// 
function parse_options(&$argc, $argv, &$options)
{
    // 
    // Get command line options.
    // 
    $args = array();
    $defaults = $options;
    
    get_opt($argv, $argc, $args);
    foreach($args as $key => $val) {
    	switch($key) {
	 case "--action":
	    if(!isset($val)) {
		die(sprintf("%s: option --base requires an argument (see --help)\n", $options->prog));
	    }
	    if($val != "get" && $val != "post" && $val != "put" && $val != "delete") {
		die(sprintf("%s: invalid argument for option --action (see --help)\n", $options->prog));
	    }
	    $options->action = $val;
	    break;
	 case "--base":
	    if(!isset($val)) {
		die(sprintf("%s: option --base requires an argument (see --help)\n", $options->prog));
	    }
	    $options->base = $val;
	    break;
	 case "-d":
	 case "--debug":           // Enable debug.
	    $options->debug = true;
	    break;
	 case "--delete":
	    $options->action = "delete";
	    break;
	 case "--format":
	    if(!isset($val)) {
		die(sprintf("%s: option --format requires an argument (see --help)\n", $options->prog));
	    }
	    $options->format = $val;
	    break;
	 case "--file":
	    if(!isset($val)) {
		die(sprintf("%s: option --file requires an argument (see --help)\n", $options->prog));
	    }
	    $options->file = $val;
	    break;
	 case "--func":
	    if(!isset($val)) {
		die(sprintf("%s: option --func requires an argument (see --help)\n", $options->prog));
	    }
	    $options->func = $val;
	    break;
	 case "--get":
	    $options->action = "get";
	    break;	    
	 case "-h":
	 case "--help":            // Show help.
	    usage($options->prog, $defaults);
	    exit(0);
	 case "--params":
	    if(!isset($val)) {
		die(sprintf("%s: option --params requires an argument (see --help)\n", $options->prog));
	    }
	    $options->params = $val;
	    break;
	 case "--post":
	    if(!isset($val)) {
		die(sprintf("%s: option --post requires an argument (see --help)\n", $options->prog));
	    }
	    $options->action = "post";
	    $options->file = $val;
	    break;
	 case "--put":
	    $options->action = "put";
	    break;
	 case "--type":
	    if(!isset($val)) {
		die(sprintf("%s: option --type requires an argument (see --help)\n", $options->prog));
	    }
	    if($val != "http" && $val != "xmlrpc" && $val != "rest") {
		die(sprintf("%s: argument for --type should be either http, xmlrpc or rest (see --help)\n", $options->prog));
	    }
	    $options->type = $val;
	    break;
	 case "-v":
	 case "--verbose":         // Be more verbose.
	    $options->verbose++;
	    break;
	 case "-V":
	 case "--version":         // Show version info.
	    version($options->prog, $options->version);
	    exit(0);	      
	 default:
	    die(sprintf("%s: unknown option '%s', see --help\n", $options->prog, $key));
	}
    }	      
}

// 
// The main function.
//
function main(&$argc, $argv)
{
    $prog = basename(array_shift($argv));
    $vers = trim(file_get_contents("../VERSION"));
    
    // 
    // Setup defaults in options array:
    // 
    $options = array( "baseurl" => "http://localhost/batchelor/ws",
		      "type"    => "http",
		      "func"    => null,
		      "file"    => null,
		      "params"  => null,
		      "format"  => "foa",  // for HTTP RPC service
		      "action"  => "get",  // for REST service
		      "debug"   => false, 
		      "verbose" => 0,
		      "prog"    => $prog, 
		      "version" => $vers );
    
    // 
    // Fill $options with command line options.
    // 
    $options = (object)$options;
    parse_options($argc, $argv, $options);

    // 
    // Dump options:
    //
    if($options->debug && $options->verbose) {
	var_dump($options);
    }

    // 
    // Call info method by default.
    // 
    if(!isset($options->func)) {
	if($options->type == "http") {
	    $options->func = "info";
	} elseif($options->type == "xmlrpc") {
	    $options->func = "batchelor.info";
	}
	if($options->debug) {
	    printf("debug: using %s as default method\n", $options->func);
	}
    }

    get_rpc_response($options);
}

// 
// Start normal script execution.
// 
main($_SERVER['argc'], $_SERVER['argv']);

?>
