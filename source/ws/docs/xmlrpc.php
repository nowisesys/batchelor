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

define("WS_NAME", "XML-RPC");

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
        echo "<p>The XML-RPC web service interface was written based on the XML-RPC ";
        echo "specification from UserLand Software. It's an traditional web service ";
        echo "protocol using HTTP as its transport protocol with the request and ";
        echo "response encoded in XML and delivered in the HTTP body (the XML payload).</p>\n";
        echo "<p><u><b>Warning:</b></u></p>\n";
        echo "<p>The XML-RPC specification was the result of a pre-dated fork of the work that later become ";
        echo "the SOAP protocol specification. The XML-RPC specification is now more or less obsolete, ";
        echo "and this paticular implementation should be considered as an experiment (even though its fully working).</p>\n";
        echo "<p>If you find yourself wondering: <i>\"Should I use this in a production environment?\"</i>, then the short answer is <i>\"No!\"</i>. ";
        echo "Use REST, SOAP or HTTP RPC instead.</p>\n";

        echo "<span id=\"secthead\">Error handling:</span>\n";
        echo "<p>The HTTP status code is always 200 and errors are encoded in the ";
        echo "XML payload.</p>\n";

        echo "<span id=\"secthead\">Specification:</span>\n";
        echo "<p>The specification used can be found here: <a href=\"http://www.xmlrpc.com/spec\">http://www.xmlrpc.com/spec</a></p>\n";

        echo "<span id=\"secthead\">Testing:</span>\n";
        echo "<p>Here are some example of using the utils/ws.php client for testing the XML-RPC protocol.</p>";
        echo "<p>Show all RPC methods:</p>\n";
        echo "<p><div class=\"code\"><pre>\n";
        echo "<code>bash$> php ws.php --type=xmlrpc --func=batchelor.info</code>\n";
        echo "</pre></div></p>\n";
        echo "<p>Describe the RPC method named batchelor.resume:</p>\n";
        echo "<p><div class=\"code\"><pre>\n";
        echo "<code>bash$> php ws.php --type=xmlrpc --func=batchelor.func --params='func=batchelor.resume'</code>\n";
        echo "</pre></div></p>\n";
        echo "<p>List all jobs finished with errors, sorted by their start time:</p>\n";
        echo "<p><div class=\"code\"><pre>\n";
        echo "<code>bash$> php ws.php --type=xmlrpc --func=batchelor.queue --params='sort=started&filter=error'</code>\n";
        echo "</pre></div></p>\n";
        echo "<p>Starting new job with simula.c as indata:</p>\n";
        echo "<p><div class=\"code\"><pre>\n";
        echo "<code>bash$> php ws.php --type=xmlrpc --func=batchelor.enqueue --post=simula.c</code>\n";
        echo "</pre></div></p>\n";
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

