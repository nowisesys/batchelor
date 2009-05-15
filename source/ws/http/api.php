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
// This script is part of the lightweight HTTP web service interface. This script
// implements the RPC-function info().
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
ws_http_session_setup();

// 
// Print function list in XML.
// 
function print_info_xml()
{
    $entries = ws_get_rpc_method_by_index();
    print "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    print "<methods>\n";
    foreach($entries as $entry) {
	printf("  <method><name>%s</name></method>\n", $entry['name']);
    }
    print "</methods>\n";
}

// 
// Print function list in FOA format.
// 
function print_info_foa()
{
    $entries = ws_get_rpc_method_by_index();
    print "[\n";
    foreach($entries as $entry) {
	printf("%s\n", $entry['name']);
    }
    print "]\n";
}

// 
// Print function list in PHP format.
// 
function print_info_php()
{
    $entries = ws_get_rpc_method_by_index();
    $names = array();
    foreach($entries as $entry) {
	$names[] = $entry['name'];
    }
    printf("%s", serialize($names));
}

// 
// Print function list in JSON format.
// 
function print_info_json()
{
    $entries = ws_get_rpc_method_by_index();
    $names = array();
    foreach($entries as $entry) {
	$names[] = $entry['name'];
    }
    printf("%s", json_encode($names));
}

// 
// Print function list as WDDX packet.
// 
function print_info_wddx()
{
    $entries = ws_get_rpc_method_by_index();
    $names = array();
    foreach($entries as $entry) {
	$names[] = $entry['name'];
    }
    printf("%s", wddx_serialize_vars("names"));
}

// 
// Print function list in human readable format.
// 
function print_info_human()
{
    $entries = ws_get_rpc_method_by_index();
    $methods = array();
    foreach($entries as $entry) {
	array_push($methods, $entry['name']);
    }
    printf("These methods exists:\n");
    printf("---------------------\n");
    printf("%s\n", implode(", ", $methods));
}

// 
// Print function list in HTML format (with clickable links).
// 
function print_info_html()
{
    $entries = ws_get_rpc_method_by_index();
    
    print "<h3>Public HTTP RPC methods:</h3>\n";
    print "<div class=\"indent\">\n";
    foreach($entries as $entry) {
	if(isset($entry['func'])) {
	    $params = null;
	    if(isset($entry['params'])) {
		foreach($entry['params'] as $key => $val) {
		    if(isset($params)) {
			$params .= "&$key=$val";
		    } else {
			$params  = "?$key=$val";
		    }
		}
	    }
	    printf("<a href=\"func?name=%s&format=html\" title=\"request broker url (show more info)\">%s</a>\n", $entry['name'], $entry['name']);
	    printf("[<a href=\"%s%s\" title=\"direct web service url (example url)\">^</a>]\n", $entry['script'], $params);
	}
    }
    print "</div>\n";
    print "<p>The public methods is used by other services for interaction with Batchelor. You can call the methods \n";
    print "either <u>direct thru its script</u> or <u>thru the request broker</u>. The request broker gives logical \n";
    print "names (like readdir) to methods and route the request to its associated script (a script can \n";
    print "be the handler for multiple methods).</p>\n";
    print "<h4>Examples:</h4>\n";
    print "<p><b>Listing result files thru the request broker:</b><br>\n";
    print "<div class=\"code\"><code>readdir?result=1234&jobid=99</code></div></p>\n";
    print "<p><b>Listing result files thru direct access:</b><br>\n";
    print "<div class=\"code\"><code>result.php?result=1234&jobid=99</code></div></p>\n";
    
    print "<h3>Internal &amp; Meta data HTTP RPC methods:</h3>\n";
    print "<div class=\"indent\">\n";
    foreach($entries as $entry) {
	if(!isset($entry['func'])) {
	    $params = null;
	    if(isset($entry['params'])) {
		foreach($entry['params'] as $key => $val) {
		    if(isset($params)) {
			$params .= "&$key=$val";
		    } else {
			$params  = "?$key=$val";
		    }
		}
	    }
	    printf("<a href=\"func?name=%s&format=html\" title=\"request broker url (show more info)\">%s</a>\n", $entry['name'], $entry['name']);
	    printf("[<a href=\"%s%s\" title=\"direct web service url (example url)\">^</a>]\n", $entry['script'], $params);
	}
    }
    print "</div>\n";
    print "<p>The internal methods (info, func and docs) don't interact with Batchelor itself, instead they are used \n";
    print "to get information to learn about the HTTP RPC web service itself.</p>\n";
    print "<p>The two methods errors and errmsg can be used to map the error code returned from a failed method call. \n";
    print "For example, use <code>errmsg?code=3</code> to get the error message for error code 3.</p>\n";
    print "</div>\n";
    
    print "<h3>Complete HTTP RPC API:</h3>\n";
    print "<p><div class=\"indent\">Click <a href=\"docs?format=html\">here</a> to view the complete API at once.</div></p>\n";
    
    print "<h3>Sending method requests</h3>\n";
    print "<h4>Parameter format:</h4>\n";
    print "<p>Parameters for methods are always submitted using standard HTTP GET or POST. \n";
    print "An additional parameter format={foa|xml|php|json|wddx} can be passed to select the format of returned data.</p>\n";
    printf("<p>The standard format used if the format parameter is missing is %s, but its \n", WS_HTTP_OUTPUT_FORMAT);
    print "up to each installation to select the default format in its configuration. No format is prefered over the others, \n";
    print "it depends on the language you use for the client side.</p>\n";
    
    print "<h3>Reading the method result</h3>\n";
    print "<p>The HTTP RPC service supports a number of different output formats that can be selected by append \n";
    print "a <code>format=name</code> parameter in the method call (request URI). Currently supported output \n";
    print "formats are:\n";
    print "<ul>\n";
    print "<li>XML   : XML document using a minimal set of tags.</li>\n";
    print "<li>FOA   : Fast Object and Array encoding (described in the manual).</li>\n";
    print "<li>PHP   : Serialized data using PHP serialize() function.</li>\n";
    print "<li>JSON  : JavaScript Object Notation (JSON) data-interchange format.</li>\n";
    print "<li>WDDX  : Web Distributed Data eXchange (WDDX) mechanism (ColdFusion).</li>\n";
    print "</ul>\n";
    print "</p>\n";
    print "<p>The native format is FOA, that stands for Fast Object and Array encoding, and is designed to be lightweight \n";
    print "and easy to scan/parse by a computer.</p>\n";
    
    print "<h4>Return values (FOA):</h4>\n";
    print "<p>The XML format of return values should be fairly easy to understand, so this section \n";
    print "will focus on describing the FOA-format. See <a href=\"http://www.wikipedia.org\">Wikipedia</a> for descriptions of the other formats.</p>\n";
    print "<p>All methods returns either nothing (void), an single value (boolean, integer or string) or \n";
    print "an compound value (array or object). The single values are fairly obvious, but arrays and objects \n";
    print "serves an disscussion.</p>\n";
    
    print "<h4>Arrays</h4>\n";
    print "<p>An array is a list containing one or more values of the same type. The notation <code>array=[string]</code> denotes that a \n";
    print "method returns an array of strings. The encoding of an array in the result is <code>name=[type]</code>, where type can be any \n";
    print "other type, including another array. An anonymous array is denoted as <code>[type]</code>.</p>\n";
    print "<p>The values in the array is separated by newline characters ('\\n').</p>\n";
    print "<p>An example array returned by a method looks like this:\n";
    print "<div class=\"code\"><pre>name=[\n\torange\n\tapple\n\tbanana\n]</pre></div></p>\n";
    
    print "<h4>Objects</h4>\n";
    print "<p>Objects is encoded as <code>name=(type)</code>, where type is any other type like integer, string or another object.\n";
    print "An anonymous object is encoded as <code>(type)</code>.</p>\n";
    print "<p>If the members of an object is known, then they are denoted in the return description as e.g. <code>person=(string,string,integer)</code>. \n";
    print "If the members are variable (like for the func method), then the return value is simply described as <code>object=()</code></p>\n";
    print "<p>An example object returned by a method looks like this:\n";
    print "<div class=\"code\"><pre>person=(\n\tfname=Albert\n\tlname=Einstein\n\tiq=160+\n)</pre></div></p>\n";
    
    print "<h3>Detecting errors:</h3>\n";
    print "<p>Errors are signaled to clients by HTTP status codes. A successful method request gets HTTP 200 (OK) back, \n";
    print "while any other problem is reported with code 3xx, 4xx or 5xx.</p>\n";
    print "<h4>HTTP status codes</h4>\n";
    print "<p>If a method fails, then HTTP 409 (Conflict) will be sent. A typical reason can be that the called method \n";
    print "failed to delete a job. If wrong parameters are passed, then the client will receive HTTP 400 (Bad Request).</p>\n";
    print "<p>If a method compares the state of the queue and it has not changed, then HTTP 304 (Not Modified) will be sent back. \n";
    print "Note that an response with HTTP 304 will <u>never</u> contain any body, this is explicit prevented by the HTTP protocol \n";
    print "specification (RFC 2616).</p>\n";
    print "<p>Using HTTP status code gives the client a fast way to detect errors without needing to decoding the response body.\n";
    print "<h4>Messages:</h4>\n";
    print "<p>The reason for an failed method call is passed in the HTTP header using the custom header entity <code>X-RPC-Error: NN</code>, \n";
    print "where NN is a number between 1 and 8. The error codes can be mapped to messages by a calling <code>error?format=foa</code> from the client.</p>\n";
    
    print "<h3>Method description:</h3>\n";
    print "<p>Quering for <code>func?format=foa&name=queue</code> will return this:</p>\n";
    print "<p><div class=\"code\"><pre>\n";
    print_func_foa(ws_get_rpc_method_by_name("queue"));
    print "</pre></div></p>\n";
    print "<p>Ignoring the type names we can read this as and object containing five members (where the first four are strings and the \n";
    print "last params member is an array). The params array itself contains objects, each containing two members.</p>\n";
    print "<h4>Return value description:</h4>\n";
    print "<p>The above object would be described as this return value in the method information:<div class=\"code\"></pre>object=(string,string,string,string,array=[object=(string,string)])</pre></div></p>\n";
    print "<p>Or even more compact as:<div class=\"code\"></pre>(string,string,string,string,[(string,string)])</pre></div></p>\n";
    
    print "<h3>Test client:</h3>\n";
    print "<p>An ordinary web browser is a bad test tool for web services, especial HTTP RPC as it uses HTTP status codes \n";
    print "that might confuse the browser. Even telnet is a better tool than a web browser.</p>\n";
    print "<h4>The utils/ws.php debug client:</h4\n";
    print "<p>Theres an utility script named utils/ws.php that can be used to debug the HTTP \n";
    print "RPC and XML-RPC interfaces. It also dumps the HTTP headers to aid in protocol tracing.</p>\n";
    
    print "<p>An example request using ws.php might look like this:</p>\n";
    print "<p><div class=\"code\"><pre>\n";
    print "andlov@darkstar$ php ws.php --func=suspend --params='result=1234&jobid=99'\n";
    print "\n";
    print "HTTP/1.0 409 Conflict\n";
    print "Date: Fri, 26 Sep 2008 15:30:21 GMT\n";
    print "Server: Apache\n";
    print "X-Powered-By: PHP/5.2.6-pl6-gentoo\n";
    print "Set-Cookie: hostid=f528764d624db129b32c21fbca0cb8d6; expires=Fri, 03-Oct-2008 15:30:21 GMT\n";
    print "X-RPC-Error: 6\n";
    print "Connection: close\n";
    print "Content-Length: 133\n";
    print "Content-Type: text/html\n";
    print "\n";
    print "false\n";
    print "</pre></div></p>\n";
    print "<p>This shows a failed request to suspend job 99, probably because the server is not configured \n";
    print "to allow job control at all.</p>\n";
}

// 
// Print function info in XML.
// 
function print_func_xml($entry)
{
    print "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    print "<method>\n";
    printf("  <name>%s</name>\n", $entry['name']);
    printf("  <desc>%s</desc>\n", $entry['desc']);
    printf("  <script>%s</script>\n", $entry['script']);
    printf("  <return>%s</return>\n", isset($entry['return']) ? $entry['return'] : "void");
    if(isset($entry['params'])) {
	print "  <params>\n";
	foreach($entry['params'] as $name => $type) {
	    printf("    <param>\n");
	    printf("      <name>%s</name>\n", $name);
	    printf("      <type>%s</type>\n", $type);
	    printf("    </param>\n");
	}
	print "  </params>\n";
    }
    print "</method>\n";
}

// 
// Print function info in FOA format.
// 
function print_func_foa($entry)
{
    printf("method=(\n");
    printf("\tname=%s\n", $entry['name']);
    printf("\tdesc=%s\n", $entry['desc']);
    printf("\tscript=%s\n", $entry['script']);
    printf("\treturn=%s\n", isset($entry['return']) ? $entry['return'] : "void");
    if(isset($entry['params'])) {
	print "\tparams=[\n";
	foreach($entry['params'] as $name => $type) {
	    printf("\t\tparam=(\n");
	    printf("\t\t\tname=%s\n", $name);
	    printf("\t\t\ttype=%s\n", $type);
	    printf("\t\t)\n");
	}
	print "\t]\n";
    }
    print ")\n";
}

// 
// Print function info in PHP format.
// 
function print_func_php($entry)
{
    printf("%s", serialize($entry));
}

// 
// Print function info in JSON format.
// 
function print_func_json($entry)
{
    printf("%s", json_encode($entry));
}

// 
// Print function info as WDDX packet.
// 
function print_func_wddx($entry)
{
    printf("%s", wddx_serialize_vars("entry"));
}

// 
// Print function info in human readable format.
// 
function print_func_human($entry)
{
    printf("Description of method %s:\n", $entry['name']);
    printf("-----------------------------------------\n");
    printf("Method name: %s\n", $entry['name']);
    printf("Description: %s\n", $entry['desc']);
    printf("Script name: %s\n", $entry['script']);
    printf("Return type: %s\n", isset($entry['return']) ? $entry['return'] : "void");
    printf("\n");
    if(isset($entry['params'])) {
	print "This method acccepts these parameters:";
	$separator = "";
	foreach($entry['params'] as $name => $type) {
	    printf("%s %s (%s)", $separator, $name, $type);
	    $separator = ",";
	}
	print "\n";
    } else {
	print "This method don't accepts any parameters.\n";
    }
}

// 
// Print function info in HTML.
// 
function print_func_html($entry)
{
    printf("<h3>Information about the HTTP RPC '%s' method:</h3>\n", $entry['name']);    
    printf("<p>%s</p><div class=\"indent\"><table>\n", $entry['desc']);
    printf("<tr><td>Method name:</td><td>%s</td></tr>\n", $entry['name']);
    printf("<tr><td>Script name:</td><td>%s</td></tr>\n", $entry['script']);
    printf("<tr><td>Returns:    </td><td>%s</td></tr>\n", isset($entry['return']) ? $entry['return'] : "void");
    printf("</table>\n");
    if(isset($entry['params'])) {
	printf("<br>Accepted parameters:<ul>\n");
	foreach($entry['params'] as $name => $type) {
	    printf("<li>%s (type: %s)</li>\n", $name, $type);
	}
	printf("</ul>\n");
    } else {
	printf("<br>This method don't accept any parameters.\n");
    }
    print "</div>\n";
}

// 
// Setup template system for printing HTML output. The loaded UI template
// will call print_html() to generate the HTML code.
// 
function print_html_page()
{
    chdir("../..");
    include("include/ui.inc");
    load_ui_template("apidoc");
}

// 
// Print the complete API documentation in HTML format.
// 
function print_docs_html()
{
    print "<h2>HTTP RPC method API documentation</h2>\n";
    print_info_html(true);
    print "<br><h2>RPC method index:</h2>\n";
    $entries = ws_get_rpc_method_by_index();
    foreach($entries as $entry) {
	print_func_html($entry, true);
    }
    print "<br><h2>Contact &amp; bug reports:</h2>\n";
    print "<p>Send any questions and bug reports to <a href=\"mailto:anders.lovgren@bmc.uu.se\">Anders L&ouml;vgren</a> (Computing Department at BMC, Uppsala University)\n";
    print "or <a href=\"mailto:lespaul@algonet.se\">Anders L&ouml;vgren</a> (QNET)</p>\n";
}

// 
// This is the callback function used by the apidoc UI template (HTML).
// 
function print_html($what)
{
    switch($what) {
     case "header":
	printf("<style type=\"text/css\">\np,h4{position:relative;left:15px;width:600px;}\ndiv.indent{position:relative;left:15px;}\nh3{color:navy;text-decoration:underline}\n#date{color:gray;}\ndiv.code{position:relative;left:35px;}\ndiv.code pre,code{font-family:courier,courier-new}\n#footer{color:#666666;}</style>\n");
	break;
     case "body":
	if($GLOBALS['name'] == "info") {
	    print_info_html();
	} else if($GLOBALS['name'] == "func") {
	    print_func_html(ws_get_rpc_method_by_name($_REQUEST['name']));
	} else {
	    print_docs_html();
	}
	break;
     case "title":
	if($GLOBALS['name'] == "info") {
	    printf("%s - HTTP RPC documentation", HTML_PAGE_TITLE);
	} else if($GLOBALS['name'] == "func") {
	    printf("%s - HTTP RPC manual - The %s method", HTML_PAGE_TITLE, $_REQUEST['name']);
	} else {
	    printf("%s - HTTP RPC manual &amp; method API", HTML_PAGE_TITLE);
	}
	break;
     default:
	print_common_html($what);
	break;
    }
}

// 
// Begin buffer output:
// 
ob_start();

if($GLOBALS['name'] == "info") {
    switch($GLOBALS['format']) {
     case "xml":
	print_info_xml();
	break;
     case "foa":
	print_info_foa();
	break;
     case "php":
	print_info_php();
	break;
     case "json":
	print_info_json();
	break;
     case "wddx":
	print_info_wddx();
	break;
     case "html":	
	print_html_page();
	break;
     case "human":
	print_info_human();
	break;
    }
} else if($GLOBALS['name'] == "func") {
    if(!isset($_REQUEST['name'])) {
	put_error("Missing parameter name");
	ws_http_error_handler(400, WS_ERROR_MISSING_PARAMETER);
    }
    $entry = ws_get_rpc_method_by_name($_REQUEST['name']);
    switch($GLOBALS['format']) {
     case "xml":
	print_func_xml($entry);
	break;
     case "foa":
	print_func_foa($entry);
	break;
     case "php":
	print_func_php($entry);
	break;
     case "json":
	print_func_json($entry);
	break;
     case "wddx":
	print_func_wddx($entry);
	break;
     case "html":
	print_html_page();
	break;
     case "human":
	print_func_human($entry);
	break;
    }
} else if($GLOBALS['name'] == "docs") {
    if($GLOBALS['format'] != "html") {
	put_error("The API documentation requires HTML format");
	ws_http_error_handler(400, WS_ERROR_INVALID_FORMAT);
    } else {
	print_html_page();
    }
} else {
    put_error(sprintf("Unexpected RPC method %s", $GLOBALS['name']));
    ws_http_error_handler(500, WS_ERROR_UNEXPECTED_METHOD);
}

header(sprintf("Content-Type: %s; charset=%s", ws_get_mime_type(), "UTF-8"));
header("Connection: close");

// 
// Flush buffered output:
// 
ob_end_flush();

?>
