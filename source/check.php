<?php

// -------------------------------------------------------------------------------
//  Copyright (C) 2007 Anders Lövgren
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
// Check if system is up to date for running batchelor.
// 
// 
// List of hosts allowed to run this script from a web browser.
// 
$trusted_hosts = array("127.0.0.1", "::1");

//
// Sanity check:
//
if (isset($_SERVER['SERVER_ADDR'])) {
        $trusted = false;
        foreach ($trusted_hosts as $host) {
                if ($_SERVER['REMOTE_ADDR'] == $host) {
                        $trusted = true;
                        break;
                }
        }
        if (!$trusted) {
                die("You are not allowed to run this script");
        }
}

// 
// Make sure we are running from the source directory.
// 
if (realpath(dirname(__FILE__)) != getcwd()) {
        die("This script should be run from inside the source directory.");
}

// 
// Include system checks.
// 
include "../include/check.inc";
include "../include/common.inc";

// 
// Running as CLI or under a web server?
// 
if (isset($_SERVER['SERVER_ADDR'])) {
        $mode = "www";
} else {
        $mode = "cli";
}

define("WPRINTF_MODE", $mode);

// 
// This function gets called when runned in www mode.
// 
function print_html($what)
{
        switch ($what) {
                case "body":
                        print "<h3>Batchelor: system check</h3><hr><pre>\n";
                        run_all_tests();
                        print "</pre>\n";
                        break;
                case "title":
                        print "Batchelor: system check";
                        break;
                default:
                        print_common_html($what);
                        break;
        }
}

if ($mode == "www") {
        include "../include/ui.inc";
        load_ui_template("popup");
} else {
        run_all_tests();
}

