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
        echo "<ul><li><a href=\"#running\">running jobs</a> (enqueue, dequeue, suspend, resume)</li>\n";
        echo "    <li><a href=\"#monitor\">monitor jobs</a> (queue, watch, stat)</li>\n";
        echo "    <li><a href=\"#reading\">reading results</a> (opendir, readdir, fopen)</li>\n";
        echo "</ul>\n";
        echo "</p>\n";
        echo "<p>The interface was developed using <a href=\"https://jax-ws.dev.java.net/\">Java JAX-WS</a> to ensure conformance ";
        echo "to relevant standards. The SOAP API of Batchelor has been tested to work with ";
        echo "various SOAP frameworks in different languages (like Java and PHP)</p>\n";

        echo "<span id=\"secthead\">Usage:</span>\n";
        echo "<p>\n";
        echo "<span id=\"subsect\">Using WSDL:</span>\n";
        printf("<p>Use the <a href=\"%s\">WSDL describing the SOAP service</a> ", get_wsdl_url());
        echo "with your favourite SOAP framework to generate code (stub methods) to connect ";
        echo "your application to the Batchelor SOAP service.</p>\n";
        echo "<p>\n";
        echo "<span id=\"subsect\">The Java library:</span>\n";
        echo "<p>\n";
        echo "You can download the <a href=\"http://it.bmc.uu.se/andlov/proj/batchelor-java/\" title=\"The BatchelorWebService library for Java developers\">client side Java (tm) library batchelor-java</a>. ";
        echo "The library is fully documented using javadoc comments.</p>\n";

        echo "<span id=\"secthead\">Example:</span>\n";
        echo "<p>Using the batchelor-java library is as simple as:</p>\n";
        echo "<p><div class=\"code\"><pre>\n";
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

        echo "<span id=\"secthead\">Method description:</span>\n";
        echo "<p>This section documents the various methods and their purpose. For those using the ";
        echo "batchelor-java library, its better to read the <a href=\"http://it.bmc.uu.se/andlov/proj/batchelor-java/javadoc/se/uu/bmc/it/batchelor/soap/BatchelorSoapClient.html\">Javadoc API documentation</a>.</p>\n";
        echo "<p><span id=\"subsect\"><a name=\"running\">Running jobs:</a></span></p>\n";
        echo "<ul><li><b>enqueue</b>: queues a new job for later execution.</li>\n";
        echo "    <li><b>dequeue</b>: removes an existing job.</li>\n";
        echo "    <li><b>suspend</b>: suspends an already running job.</li>\n";
        echo "    <li><b>resume</b>: resumes an suspended job.</li>\n";
        echo "</ul>\n";
        echo "<p><span id=\"subsect\"><a name=\"monitor\">Monitor jobs:</a></span></p>\n";
        echo "<ul><li><b>queue</b>: list content of the queue.</li>\n";
        echo "    <li><b>watch</b>: get list of jobs finished after a timestamp.</li>\n";
        echo "    <li><b>stat</b>: get status of a single job.</li>\n";
        echo "</ul>\n";
        echo "<p><span id=\"subsect\"><a name=\"reading\">Reading results:</a></span></p>\n";
        echo "<ul><li><b>opendir</b>: get list of job directories.</li>\n";
        echo "    <li><b>readdir</b>: get list of files in a single job directory.</li>\n";
        echo "    <li><b>fopen</b>: read a file from the job directory.</li>\n";
        echo "</ul>\n";
        echo "<p>The in and out types of each method can be read on the <a href=\"soap_types.php\">SOAP types</a> page.</p>\n";

        echo "<span id=\"secthead\">Optimizing WSDL:</span>\n";
        echo "<p>The WSDL is generated dynamic by default from source/ws/schema/wsdl/batchelor.wsdl by ";
        echo "substituting the soap:address location and the SOAP type schema location ";
        echo "(a split WSDL). The dynamic generation can be <u>disabled</u> by putting a pre-parsed version of ";
        echo "batchelor.wsdl named batchelor.wsdl.cache under source/ws/schema/wsdl/. If it exist, then its ";
        echo "sent \"as is\".</p>\n";

        echo "<span id=\"secthead\">Testing:</span>\n";
        echo "<p>The SOAP service can be tested by using the CLI web service utility (included with the <a href=\"http://it.bmc.uu.se/andlov/proj/batchelor/download.php\">source code</a> for batchelor). Run ";
        echo "the utility like this to dump the queue sorted on job ID:</p>\n";
        echo "<p><div class=\"code\"><pre>\n";
        echo "<code>bash$> cd utils</code>\n";
        echo "<code>bash$> php ws.php --type=soap --func=queue --params='sort=jobid&amp;filter=all'</code>\n";
        echo "</pre></div></p>";
        echo "<p>This command shows the available remote methods (attach -v to also see the ";
        echo "method argument types):</p>\n";
        echo "<p><div class=\"code\"><pre>\n";
        echo "<code>bash$> php ws.php --type=soap -d</code>\n";
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
?>
