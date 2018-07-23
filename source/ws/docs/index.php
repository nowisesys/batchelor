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

// 
// Set defaults for web service status.
// 
if (!defined("WS_ENABLE_HTTP")) {
        define("WS_ENABLE_HTTP", null);
}
if (!defined("WS_ENABLE_SOAP")) {
        define("WS_ENABLE_SOAP", null);
}
if (!defined("WS_ENABLE_REST")) {
        define("WS_ENABLE_REST", null);
}
if (!defined("WS_ENABLE_XMLRPC")) {
        define("WS_ENABLE_XMLRPC", null);
}

include "include/ws.inc";

function print_title()
{
        printf("%s - Web Services", HTML_PAGE_TITLE);
}

function print_body()
{
        printf("<h2><img src=\"../../icons/nuvola/info.png\"> %s - Web Services</h2>\n", HTML_PAGE_TITLE);
        echo "<span id=\"secthead\">Status:</span>\n";
        ws_print_services_status("../../");
        echo "<p>See <a href=\"intro.php\">introduction</a> for getting started information.</p>\n";
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

