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
// Shows information on the web service ... 
// 

ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . "../../.." . PATH_SEPARATOR . "..");

define("WS_NAME", "HTTP RPC");

//
// Get configuration.
// 
include "conf/config.inc";
include "include/ui.inc";

function print_title() 
{
    printf("%s - Web Services API (%s)", HTML_PAGE_TITLE, WS_NAME);
}

function print_body()
{
    printf("<h2><img src=\"../../icons/nuvola/info.png\"> %s - Web Services API (%s)</h2>\n", HTML_PAGE_TITLE, WS_NAME);
    
    echo "<span id=\"secthead\">Introduction:</span>\n";
    echo "<p>This is a lightweight protocol that uses URI encoded requests sent either ";
    echo "to the request broker or direct to the script thru HTTP. The output (response) ";
    echo "format is configurable on the server and can also be selected by the ";
    echo "caller (the client). The output formats that can be selected are:</p>\n";
    echo "<div class=\"indent\"><table>\n";
    echo "<tr><td><b>XML</b>:</td><td>XML format using a minimal set of tags.</td></tr>\n";
    echo "<tr><td><b>FOA</b>:</td><td>Fast Object and Array encoding (described in the manual).</td></tr>\n";
    echo "<tr><td><b>PHP</b>:</td><td>Serialized data using PHP serialize() function.</td></tr>\n";
    echo "<tr><td><b>JSON</b>:</td><td>JavaScript Object Notation (JSON) data-interchange format.</td></tr>\n";
    echo "</table></div>\n";
    echo "<p>The default format is <a href=\"http://it.bmc.uu.se/andlov/proj/foa/\">FOA</a>.</p>\n";
   
    echo "<span id=\"secthead\">Manual:</span>\n";   
    echo "<p>The full manual is available and browsable online by visiting: <a href=\"../http/docs?format=html\">ws/http/docs?format=html</a></p>\n";
    
    echo "<span id=\"secthead\">Examples:</span>\n";   
    echo "<p>An request to list all queued jobs (thru the request broker) looks ";
    echo "like this:</p>\n";
    echo "<p><div class=\"indent\"><pre>\n";
    echo "  <code>http://localhost/batchelor/ws/http/queue?format=xml</code>\n";
    echo "</pre></div></p>\n";
    
    echo "<span id=\"secthead\">Request Broker vs. Script Direct:</span>\n";   
    echo "<p>Listing result files thru the request broker:</p>\n";
    echo "<p><div class=\"indent\"><pre>\n";    
    echo "  <code>http://localhost/batchelor/ws/http/readdir?result=1234&jobid=99</code>\n";
    echo "</pre></div></p>\n";
    echo "<p>Listing result files thru direct access:</p>\n";     
    echo "<p><div class=\"indent\"><pre>\n";
    echo "  <code>http://localhost/batchelor/ws/http/result.php?result=1234&jobid=99</code>\n";
    echo "</pre></div></p>\n";
   
    echo "<span id=\"secthead\">Methods:</span>\n";   
    echo "<p>These methods are the called to interact with the queue:</p>\n";
    echo "<div class=\"indent\"><table>\n";
    echo "<tr><td><b>suspend</b>:</td><td>Suspend an already running job.</td></tr>\n";
    echo "<tr><td><b>resume</b>:</td><td>Resume a job thats in paused or stopped state.</td></tr>\n";
    echo "<tr><td><b>enqueue</b>:</td><td>Enqueue and start new job.</td></tr>\n";
    echo "<tr><td><b>dequeue</b>:</td><td>Delete an running or finished job.</td></tr>\n";
    echo "<tr><td><b>queue</b>:</td><td>List queued and finished jobs.</td></tr>\n";
    echo "<tr><td><b>watch</b>:</td><td>Monitor queue for finished jobs.</td></tr>\n";
    echo "<tr><td><b>opendir</b>:</td><td>List result directories.</td></tr>\n";
    echo "<tr><td><b>readdir</b>:</td><td>List files in a specific result directory.</td></tr>\n";
    echo "<tr><td><b>fopen</b>:</td><td>Get content of an result file.</td></tr>\n";
    echo "<tr><td><b>stat</b>:</td><td>Provides stat of an enqueued job.</td></tr>\n";
    echo "</table></div>\n";
    echo "<p>These (meta data) methods provides info about the HTTP RPC service:</p>\n";
    echo "<div class=\"indent\"><table>\n";
    echo "<tr><td><b>info</b>:</td><td>List all methods thats part of the API.</td></tr>\n";
    echo "<tr><td><b>func</b>:</td><td>Show detailed information about a single RPC method.</td></tr>\n";
    echo "<tr><td><b>docs</b>:</td><td>Show the HTTP RPC method API as manual.</td></tr>\n";
    echo "</table></div>\n";
    echo "<p>These methods are for error handling:</p>\n";
    echo "<div class=\"indent\"><table>\n";
    echo "<tr><td><b>errors</b>:</td><td>Return an collection of all defined errors (codes and messages).</td></tr>\n";
    echo "<tr><td><b>errmsg</b>:</td><td>Return error message string for an given error code.</td></tr>\n";
    echo "</table></div>\n";
    echo "<p>The func method can be used by a web service client to find out how to ";
    echo "call any of the methods. Try it out by either running CLI:</p>\n";
    echo "<p><div class=\"indent\"><pre>\n";   
    echo "  <code>bash$> cd utils</code>\n";
    echo "  <code>bash$> php ws.php --func=func --params='name=readdir'</code>\n";
    echo "\n";
    echo "  -- or HTTP: ---\n";
    echo "\n";
    echo "  <code>http://localhost/batchelor/ws/http/func?name=readdir</code>\n";
    echo "</pre></div></p>\n";
    echo "<p>See the manual for more information.</p>\n";
   
    echo "<span id=\"secthead\">Error handling:</span>\n";
    echo "<p>The HTTP status code 200 gets returned for any successful method call, ";
    echo "that is, if everything worked out as excepted. Anything else is ";
    echo "considered and error.</p>\n";
    echo "<p>Errors are reported with these HTTP status codes:</p>\n";
    echo "<ul><li>400 (Bad Request)</li>\n";
    echo "    <li>403 (Forbidden)</li>\n";
    echo "    <li>404 (Not Found)</li>\n";
    echo "    <li>409 (Conflict)</li>\n";
    echo "    <li>500 (Internal Server Error)</li>\n";
    echo "</ul>\n";
    echo "<p>These error code are a quick way to find out if any problem occured.</p>\n";
    echo "<p>The error details are in the custom HTTP entiety X-RPC-Error: NN, where NN ";
    echo "is an error number. The error numbers are defined in include/ws.inc, but its ";
    echo "associated error message is accessable from the client by sending another ";
    echo "query to:</p>\n";
    echo "<p><div class=\"indent\"><pre>\n";   
    echo "  <code>http://localhost/batchelor/ws/http/errmsg?code=nnn</code>\n";
    echo "</pre></div></p>\n";
    echo "<p>If you get anything else than HTTP status 200 and if X-RPC-Error is set, then its an ";
    echo "method call error.</p>\n";
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
