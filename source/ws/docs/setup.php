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
// Shows requirement and setup information.
// 

ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . "../../.." . PATH_SEPARATOR . "..");

//
// Get configuration.
// 
include "conf/config.inc";
include "include/ui.inc";

function print_title() 
{
    printf("%s - Web Services - Setup", HTML_PAGE_TITLE);
}

function print_body()
{
    printf("<h2><img src=\"../../icons/nuvola/info.png\"> %s - Web Services - Setup</h2>\n", HTML_PAGE_TITLE);    

    echo "<span id=\"secthead\">Requirements:</span>\n";
    
    echo "<p><span id=\"subsect\">The SOAP extension:</span></p>\n";
    echo "<p>The PHP soap extension is required by the SOAP service. This extension was ";
    echo "added in PHP 5 and from what I know theres no way getting it to work in PHP 4.</p>";
    
    echo "<p><span id=\"subsect\">The JSON extension:</span></p>\n";
    echo "<p>The PHP json extension is required for HTTP RPC to send output encoded in ";
    echo "JSON format. If this extension is missing, then the queue view update will fall ";
    echo "back on using 'HTTP meta refresh'.</p>\n";
    echo "<p>This extension can be made to work with PHP 4 by following these simple ";
    echo "steps (instructions for Gentoo Linux):</p>\n";
    echo "<p><div class=\"code\"><pre>\n";
    echo "bash$> tar xfvj php-5.2.8.tar.bz2\n";
    echo "bash$> cp -a php-5.2.8/ext/json .\n";
    echo "bash$> cd json\n";
    echo "bash$> phpize\n";
    echo "bash$> ./configure\n";
    echo "bash$> make\n";
    echo "bash$> make install\n";
    echo "bash$> echo \"extension=json.so\" &gt /etc/php/apache2-php4/ext/json.ini\n";
    echo "bash$> cd /etc/php/apache2-php4/ext-active\n";
    echo "bash$> ln -s /etc/php/apache2-php4/ext/json.ini .\n";
    echo "</pre></div></p>\n";
    echo "<p>Restart the web server so that the JSON extension gets loaded.</p>\n";

    echo "<span id=\"secthead\">Setup:</span>\n";
    echo "<p>The web server must be configured to allow the various web services under ";
    echo "source/ws/ to be callable. For Apache this is done inside conf/config.inc ";
    echo "and by appending -D WEB_SERVICE to Apache's command line options. ";    
    echo "In Gentoo, the -D WEB_SERVICE define to Apache can be set in the config ";
    echo "file /etc/conf.d/apache2</p>\n";
   
    echo "<p>Make sure to enable thoose web service protocol/interface you like to use ";
    echo "in <code>conf/config.inc</code> and also set access permissions in <code>conf/apache.conf</code>. ";
    echo "By default, all web services are locked down to local host access, but it highly ";
    echo "recommended that at least HTTP RPC is configured with world wide access.</p>\n";
    
    echo "<p><span id=\"subsect\">Ajax and refresh of the queue view:</span></p>\n";
    echo "<p>The HTTP RPC interface is used together with Ajax technology to keep the queue ";
    echo "view updated as the state of queued jobs changes (i.e. going from pending to running). ";
    echo "If the HTTP RPC service is disabled, the the queue view update will fall back on ";
    echo "using 'HTTP meta refresh' instead, which is an bad alternative.</p>\n";
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
