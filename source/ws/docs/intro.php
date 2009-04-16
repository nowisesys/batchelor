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
// Shows information on the various web service interfaces that can
// be used (if enabled).
// 

ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . "../../.." . PATH_SEPARATOR . "..");

//
// Get configuration.
// 
include "conf/config.inc";
include "include/ui.inc";

if(!defined("WS_ENABLE_HTTP")) {
    define ("WS_ENABLE_HTTP", false);
}
if(!defined("WS_ENABLE_XMLRPC")) {
    define ("WS_ENABLE_XMLRPC", false);
}
if(!defined("WS_ENABLE_REST")) {
    define ("WS_ENABLE_REST", false);
}
if(!defined("WS_ENABLE_SOAP")) {
    define ("WS_ENABLE_SOAP",   true);
}

function print_title() 
{
    printf("%s - Web Services", HTML_PAGE_TITLE);
}

function print_body()
{
    printf("<h2><img src=\"../../icons/nuvola/info.png\"> %s - Web Services</h2>\n", HTML_PAGE_TITLE);    
    
    echo "<span id=\"secthead\">Introduction:</span>\n";
    echo "<p>Version 0.7.0 of Batchelor is the first release with support for web services. The API let ";
    echo "users to consume Batchelors web service in the spirit of W3C's web service ";
    echo "definition: <i>\"a software system designed to support interoperable ";
    echo "machine-to-machine interaction over a network\".</i></p>\n";
    
    echo "<p>Batchelor defines an internal API (see ws_xxx() in include/ws.inc) that is exposed to web service clients ";
    echo "thru different Web Service protocols/interfaces. The interfaces provided ";
    echo "are:</p>\n";
    
    echo "<div class=\"info\"><table>\n";
    echo "<tr><td><b>XML-RPC:</b></td><td><a href=\"http://www.xmlrpc.com/spec\">Following UserLand Software's specification.</a></td></tr>\n";
    echo "<tr><td><b>HTTP RPC:</b></td><td><a href=\"../http/docs?format=html\">A lightweight RPC over HTTP.</a></td></tr>\n";
    echo "<tr><td><b>REST:</b></td><td><a href=\"http://www.xml.com/pub/a/2004/08/11/rest.html\">Representational State Transfer</a> (see <a href=\"http://en.wikipedia.org/wiki/Representational_State_Transfer\">Wikipedia</a>).</td></tr>\n";
    echo "<tr><td><b>SOAP:</b></td><td><a href=\"http://en.wikipedia.org/wiki/SOAP\">Simple Object Access Protocol.</a></td></tr>\n";
    echo "</table></div>\n";
    
    echo "<p>All Web Service interfaces are located under source/ws/.</p>\n";

    echo "<span id=\"secthead\">Setup:</span>\n";
    echo "<p>The web server must be configured to allow the various web services under ";
    echo "source/ws/ to be callable. For Apache this is done inside conf/config.inc ";
    echo "and by appending -D WEB_SERVICE to Apache's command line options. ";    
    echo "In Gentoo, the -D WEB_SERVICE define to Apache can be set in the config ";
    echo "file /etc/conf.d/apache2</p>\n";
   
    echo "<p>Make sure to set permissions in conf/config.inc for thoose web service ";
    echo "interfaces you like to enable. By default, all web services are locked ";
    echo "down to localhost access.</p>\n";

    echo "<span id=\"secthead\">Testing:</span>\n";
    echo "<p>An client for testing the Web Services are provided in utils/ws.php. ";
    echo "This client let you see all headers in the response for debuging purposes.</p>\n";
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