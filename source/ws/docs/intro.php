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
// Shows information on the various web service interfaces that can
// be used (if enabled).
// 

ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . "../../.." . PATH_SEPARATOR . "..");

//
// Get configuration.
// 
include "conf/config.inc";
include "include/ui.inc";

function print_title()
{
        printf("%s - Web Services - Introduction", HTML_PAGE_TITLE);
}

function print_body()
{
        printf("<h2><img src=\"../../icons/nuvola/info.png\"> %s - Web Services - Introduction</h2>\n", HTML_PAGE_TITLE);

        echo "<span id=\"secthead\">Introduction:</span>\n";
        echo "<p>Batchelor provides a fairly complete web service API that let it's users consume the ";
        echo "batchelor job queue service in the spirit of W3C's definition of a web service: ";
        echo "<i>\"a software system designed to support interoperable machine-to-machine interaction over a network\".</i></p>\n";

        echo "<p>Batchelor defines an internal API (see ws_xxx() in include/ws.inc) that is exposed ";
        echo "to web service clients thru the different web service protocols/interfaces. The same internal ";
        echo "API is also used by the web (www) frontend, this ensure that the internal state of the system is allways in sync no ";
        echo "matter which public interface is used. This picture shows this in a schematic way:<br>";
        echo "<img src=\"../../images/ws_api.png\"><br>\n";
        echo "<i>Picture showing the relation between the public interface, the internal API and Batchelor core system (bottom).</i></p>\n";

        echo "<p>The interfaces provided are:\n";
        echo "<div class=\"info\"><table>\n";
        echo "<tr><td><b>XML-RPC:</b></td><td><a href=\"http://www.xmlrpc.com/spec\">Following UserLand Software's specification.</a></td></tr>\n";
        echo "<tr><td><b>HTTP RPC:</b></td><td><a href=\"../http/docs?format=html\">A lightweight RPC over HTTP</a> (used internally with Ajax)</td></tr>\n";
        echo "<tr><td><b>REST:</b></td><td><a href=\"http://www.xml.com/pub/a/2004/08/11/rest.html\">Representational State Transfer</a> (see <a href=\"http://en.wikipedia.org/wiki/Representational_State_Transfer\">Wikipedia</a>).</td></tr>\n";
        echo "<tr><td><b>SOAP:</b></td><td><a href=\"http://en.wikipedia.org/wiki/SOAP\">Simple Object Access Protocol.</a></td></tr>\n";
        echo "</table></div></p>\n";

        echo "<p>All Web Service interfaces are located under source/ws/.</p>\n";

        echo "<span id=\"secthead\">Testing:</span>\n";
        echo "<p>An client for testing the Web Services are provided in utils/ws.php. ";
        echo "This client let you see all headers in the response for debuging purposes ";
        echo "if you use the '-d' option.</p>\n";
        echo "<p><span id=\"subsect\">Notes for users of PHP 4:</span></p>\n";
        echo "<p>The ws.php utility can't be used out of the box with PHP 4, you have to ";
        echo "create an compatible version first. This is due to missing support ";
        echo "for the try/catch keywords, causing the script compilation to fail.</p>\n";
        echo "<p>An PHP 4 compatible version is built through these steps:</p>\n";
        echo "<p><div class=\"code\"><pre>\n";
        echo "bash$> cd utils\n";
        echo "bash$> make ws_php4\n";
        echo "</pre></div></p>\n";
        echo "<p>The make target should now have created an PHP 4 compatible version of ";
        echo "ws.php named ws_php4.php</p>\n";
}

function print_html($what)
{
        switch ($what) {
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

