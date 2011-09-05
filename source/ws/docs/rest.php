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

define("WS_NAME", "REST");

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
    echo "<p>This page documents the implementation of the REST web service ";
    echo "architecture for Batchelor based on information from these document:</p>\n";
    echo "<ul><li><a href=\"http://www.xfront.com/REST-Web-Services.html\">http://www.xfront.com/REST-Web-Services.html</a></li>\n";
    echo "    <li><a herf=\"http://www.xfront.com/REST-Web-Services.html\">http://www.xml.com/pub/a/2004/08/11/rest.html</a></li>\n";
    echo "</ul>\n";

    echo "<span id=\"secthead\">The resource space:</span>\n";
    echo "<p>The REST web services is presented as a tree of URI's. Each URI has one ";
    echo "or more associated HTTP action(s) (standard GET, POST, PUT or DELETE). ";
    echo "All GET requests are non-modifying. An URI (a node resource) can be ";
    echo "changed by using PUT or POST (add or modified) or DELETE (remove).</p>\n";

    echo "<span id=\"secthead\">Response:</span>\n";   
    echo "<p>The HTTP status code is always 200. The response is always wrapped inside ";
    echo "result tags, where the state attribute is either \"success\" or \"failed\". ";
    echo "The content (the embedded message) is either an list of links or an object, ";
    echo "where the object is the requested data, an status message or an error ";
    echo "object.</p>\n";
    echo "<p>An example error message (missing method) looks like this:</p>\n";
    echo "<p><div class=\"code\"><pre>\n";
    echo "&lt;tns:result state=\"failed\" type=\"error\"&gt;\n";
    echo "  &lt;error&gt;\n";
    echo "    &lt;code&gt;3&lt;/code&gt;\n";
    echo "    &lt;message&gt;No such method&lt;/message&gt;\n";
    echo "  &lt;/error&gt;\n";
    echo "&lt;/tns:result&gt;\n";
    echo "</pre></div></p>\n";
    echo "<p>The response encoding is either XML or FOA selectable by appending ";
    echo "encode={xml|foa} to the request string. The default response encoding is XML. ";
    echo "The XML encoded response is formalized by the <a href=\"../schema/rest/\">XML Schema</a>  for REST responses. ";
    echo "The <a href=\"http://it.bmc.uu.se/andlov/proj/libfoa/spec.php\">FOA specification</a> describes the FOA encoded response format.</p>\n";

    echo "<span id=\"secthead\">Ouput format:</span>\n";   
    echo "<p>The output format from a GET request on an URI is either an list (of ";
    echo "links) or data (possibly multiple objects). The format is selected in ";
    echo "two ways: either append format={list|data} to the request URI or append ";
    echo "the format to the URI path.</p>\n";   
    echo "<p><span id=\"subsect\">Example:</span></p>\n";
    echo "<p><div class=\"code\"><pre>\n";   
    echo "<code>/queue/all?format=data      /* get all jobs */</code>\n";
    echo "<code>/queue/all/data             /* alternative way */</code>\n";
    echo "</pre></div></p>\n";
    echo "<p>An modifying HTTP action (PUT, POST, DELETE) will either return a status ";
    echo "message or data depending on the URI. Heres an example response for dequeue (removing) a job:</p>\n";
    echo "<p><div class=\"code\"><pre>\n";   
    echo "&lt;tns:result state=\"success\" type=\"status\"&gt;\n";
    echo "  &lt;status&gt;Removed job 1355&lt;/status&gt;\n";
    echo "&lt;/tns:result&gt;\n";
    echo "</pre></div></p>\n";

    echo "<span id=\"secthead\">Resource links:</span>\n";   
    echo "<p>An link has possible action attributes like get, put, post and delete. ";
    echo "The action attribute value describes the object returned by taking this action.</p>\n";
    echo "<p><span id=\"subsect\">Example:</span></p>\n";
    echo "<p><div class=\"code\"><pre>\n";   
    echo "&lt;tns:result state=\"success\" type=\"link\"&gt;\n";
    echo "  &lt;link xlink:href=\"/queue\" get=\"link\" put=\"job\" /&gt;\n";
    echo "  &lt;link xlink:href=\"/result\" get=\"link\" /&gt;\n";
    echo "    ...\n";
    echo "&lt;/tns:result&gt;\n";
    echo "</pre></div></p>\n";   
    echo "<p>The XML snippet above tells us that the /queue URI supports GET and PUT, ";
    echo "whereas the /result URI only accepts GET.</p>\n";

    echo "<span id=\"secthead\">Schematic overview:</span>\n";   
    echo "<p>Schematic overview of the resources with theirs accepted actions (HTTP ";
    echo "request method) on the right.</p>\n";

    echo "<p><div class=\"code\"><pre>\n";   
    echo "Node:                    Action:       Description:\n";
    echo "------                   --------      -------------\n";
    echo "\n";
    echo "root/                    GET           (the ws/rest service root)\n";
    echo "+-- queue/               GET,PUT,POST  (get sort and filter, enqueue with PUT/POST(1))\n";
    echo "|      +-- all/          GET,DELETE    (get or delete all objects)\n";
    echo "|            +-- xxx/    GET,DELETE    (get or delete single job)\n";
    echo "|      +-- sort/         GET           (2)\n";
    echo "|            +-- xxx/    GET           (various sort options)\n";
    echo "|      +-- filter/       GET           (2)\n";
    echo "|            +-- xxx/    GET,DELETE    (get or delete all jobs matching filter)\n";
    echo "+-- result/              GET           (list all job directories)\n";
    echo "|      +-- dir/          GET           (list result files)\n";    
    echo "|            +-- &lt;file&gt;  GET           (get content of result file)\n";
    echo "+-- watch/               POST          (watch jobs)\n";
    echo "|      +-- &lt;job&gt;         GET           (get info about job)\n";
    echo "+-- errors/              GET           (list all error types)\n";
    echo "|      +-- &lt;error&gt;       GET           (get error object)\n";
    echo "+-- suspend/             GET           (list suspendable jobs)\n";
    echo "|      +-- &lt;job&gt;         GET,POST      (get info or suspend the job)\n";
    echo "+-- resume/              GET           (list resumable jobs)\n";
    echo "       +-- &lt;job&gt;         GET,POST      (get info or resume the job)\n";
    echo "</pre></div></p>\n";   
    
    echo "<p>(1) Note that POST for enqueue new jobs is not stricly RESTful, but we ";
    echo "allows it because not all web servers supports HTTP PUT (Apache does).</p>\n";
    echo "<p>(2) Both sort and filter URI accepts an additional companion request ";
    echo "parameter, i.e. <code>'queue/sort/jobid/data?filter=error'</code> gives all error state ";
    echo "jobs filtered on the job ID.</p>\n";

    echo "<span id=\"secthead\">Enqueue new jobs:</span>\n";   
    echo "<p>All tasks except for starting new jobs (enqueue) should be fairly ";
    echo "obvious, so I will only outline the details on using PUT/POST and the /queue resource to start ";
    echo "new jobs.</p>\n";   
    echo "<p>Because the indata might be arbitrary large, the data has to be uploaded ";
    echo "thru PUT or POST. Encoding the data in the URL is on a typical system ";
    echo "limited to 32kB, not to mention it goes against REST principles also.\n";
    
    echo "<p>This is how to do HTTP PUT with PHP's cURL library:</p>\n";    
    echo "<p><div class=\"code\"><pre>\n";
    echo "curl_setopt(\$curl, CURLOPT_PUT, 1);\n";
    echo "curl_setopt(\$curl, CURLOPT_INFILE, fopen(\$options->file, \"r\"));\n";
    echo "curl_setopt(\$curl, CURLOPT_INFILESIZE, filesize(\$options->file));\n";
    echo "</pre></div></p>\n";   
    
    echo "<p>This is how to do such an POST using PHP's cURL library. The data ";
    echo "must be posted in a multipart/form-data named 'file'.</p>\n";
    echo "<p><div class=\"code\"><pre>\n";   
    echo "\$post = array(\n";
    echo "    'file' => sprintf(\"@%s\", \$options->file)  // Notice the '@'!\n";
    echo ");\n";
    echo "curl_setopt(\$curl, CURLOPT_POST, 1);\n";
    echo "curl_setopt(\$curl, CURLOPT_POSTFIELDS, \$post);\n";
    echo "</pre></div></p>\n";   
    
    echo "<span id=\"secthead\">Java library:</span>\n";
    echo "<p>You can download the <a href=\"http://it.bmc.uu.se/andlov/proj/batchelor-java/\" title=\"The BatchelorWebService library for Java developers\">client side Java (tm) library batchelor-java</a>. ";
    echo "Both SOAP and REST is supported by the library. However, the REST implementation contains more features, including using FOA for stream oriented downloads of result files. \n";
    echo "The library is fully documented using javadoc comments.</p>\n";
    
    echo "<span id=\"secthead\">Testing:</span>\n";   
    echo "<p>The web service utility (utils/ws.php) can be used to browse the REST ";
    echo "service. Start with: 'php ws.php --type=rest --params='' and then append ";
    echo "the relative URI path in the params option. The default output is XML, but ";
    echo "FOA output can be requested by appending 'encode=foa' to the request parameters.</p>\n";
    echo "<p>Browsing the root requesting FOA encoded output requires that the ";
    echo "method argument (a virtual method) is explicit set:</p>\n";
    echo "<p><div class=\"code\"><pre>\n";   
    echo "bash$> php ws.php --type=rest --params='root?encode=foa'\n";
    echo "</pre></div></p>\n";
    echo "<p>Enqueue a new job with data.txt as indata is done by putting (PUT) the file on the queue URI:</p>\n";
    echo "<p><div class=\"code\"><pre>\n";   
    echo "bash$> php ws.php --type=rest --file=data.txt --put --params='queue'\n";
    echo "</pre></div></p>\n";
    echo "<p>Watch jobs for completion. This variant includes sending the timestamp using POST.</p>\n";
    echo "<p><div class=\"code\"><pre>\n";   
    echo "bash$> php ws.php --type=rest --post='stamp=1241490725' --params='watch'\n";
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
