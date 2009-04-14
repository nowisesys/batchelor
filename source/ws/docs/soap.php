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

define("WS_NAME", "SOAP");

//
// Get configuration.
// 
include "conf/config.inc";
include "include/ui.inc";
include "include/soap.inc";

function print_title() 
{
    printf("%s - Web Services API (%s)", HTML_PAGE_TITLE, WS_NAME);
}

function print_body()
{
    printf("<h2><img src=\"../../icons/nuvola/info.png\"> %s - Web Services API (%s)</h2>\n", HTML_PAGE_TITLE, WS_NAME);    
    echo "<span id=\"secthead\">Introduction:</span>\n";
    echo "<p>\n";
    echo "Batchelor supports the SOAP protocol and have been successful integrated ";
    echo "with PHP and Java service consumers (client applications).</p>\n";
    echo "<p>The SOAP interface consists of methods for:\n";
    echo "<ul><li>running jobs (enqueue, dequeue, suspend, resume)</li>\n";
    echo "    <li>monitor jobs (queue, watch, stat)</li>\n";
    echo "    <li>reading results (opendir, readdir, fopen)</li>\n";
    echo "</ul>\n";
    echo "</p>\n";
    echo "<p>The interface was developed using <a href=\"https://jax-ws.dev.java.net/\">Java JAX-WS</a> to ensure conformance ";
    echo "to relevant standards. The SOAP API of Batchelor has been tested to work with ";
    echo "various SOAP frameworks in different languages (like Java and PHP)</p>\n";
    
    echo "<span id=\"secthead\">Usage:</span>\n";
    echo "<p>\n";
    echo "<b><u>Using WSDL:</u></b>\n";
    printf("<p>The WSDL describing the SOAP service is found <a href=\"%s\">here</a>. ", get_wsdl_url());
    echo "Use your favourite SOAP framework to generate code (stub methods) to ";
    echo "connect your application to the Batchelor SOAP service using this WSDL.</p>\n";
    echo "<p>\n";
    echo "<b><u>Using the Java library:</u></b>\n";
    echo "<p>\n";
    echo "You can download the client side Java (tm) library <a href=\"http://it.bmc.uu.se/andlov/proj/batchelor-java/\" title=\"The BatchelorWebService library for Java developers\">batchelor-java</a>. ";
    echo "The library is fully documented using javadoc comments.</p>\n";
    
    echo "<span id=\"secthead\">Example:</span>\n";
    echo "<p>Using the batchelor-java library is as simple as:</p>\n";
    echo "<p><div class=\"indent\"><pre>\n";
    echo "import se.uu.bmc.it.batchelor.*;\n";
    echo "import se.uu.bmc.it.batchelor.soap.*;\n";
    echo "\n";
    echo "class Client {\n";
    echo "\n";
    echo "    private BatchelorSoapClient service;\n";
    echo "\n";
    echo "    Client(URL url) {\n";
    echo "        service = new BatchelorSoapClient(url);  // Use WSDL as URL.\n";
    echo "    }\n";
    echo "\n";
    echo "    // ... methods calling service.XXX()\n";
    echo "\n";
    echo "}\n";    
    echo "</pre></div></p>\n";
    echo "<p>\n";
    echo "</p>\n";

    echo "<span id=\"secthead\">Optimizing WSDL:</span>\n";
    echo "<p>The WSDL is generated dynamic by default from source/ws/wsdl/batchelor.wsdl by ";
    echo "substituting the soap:address location and the SOAP type schema location ";
    echo "(a split WSDL). The dynamic genration can be <u>disabled</u> by putting a pre-parsed version of ";
    echo "batchelor.wsdl named source/ws/wsdl/batchelor.wsdl. If it exist, then its ";
    echo "sent \"as is\".</p>\n";
    
    echo "<span id=\"secthead\">Testing:</span>\n";
    echo "<p>The SOAP service can be tested by using the web service utility. Run ";
    echo "the utility like this to dump the queue sorted on job ID:</p>\n";
    echo "<p><div class=\"indent\"><pre>\n";
    echo "<code>bash$> cd utils</code>\n";
    echo "<code>bash$> php ws.php --type=soap --func=queue --params='sort=jobid'</code>\n";
    echo "</pre></div></p>";
    echo "<p>This command shows the available remote methods (attach -v to also see the ";
    echo "method argument types):</p>\n";
    echo "<p><div class=\"indent\"><pre>\n";
    echo "<code>bash$> php ws.php --type=soap -d</code>\n";
    echo "</pre></div></p>\n";
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
