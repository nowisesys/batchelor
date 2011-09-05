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
// A script for sending a picture from the statistics directory to peer.
// 

include "../conf/config.inc";
include "../include/common.inc";

// 
// Call exit on script if request parameter name is set but
// don't match the pattern.
// 
function check_request_param($name, $pattern)
{
        if (isset($_REQUEST[$name])) {
                if (!preg_match("/^$pattern$/", $_REQUEST[$name])) {
                        error_log(sprintf("Request parameter %s do not match regexp pattern %s", $name, $pattern));
                        exit(1);
                }
        }
}

// 
// Get or set the hostid cookie.
// 
update_hostid_cookie();

// 
// Validate request parameters.
// 
check_request_param("stat", "(glob|pers|load)");
check_request_param("year", "(19|20)\\d{2}");
check_request_param("month", "(0[1-9]|1[0-2])");
check_request_param("day", "(0[1-9]|[12][0-9]|3[0-1])");
check_request_param("image", "\w+");

// 
// Build the path:
// 
$image = sprintf("%s/stat", CACHE_DIRECTORY);

if (isset($_REQUEST['stat'])) {
        switch ($_REQUEST['stat']) {
                case "glob":
                case "load":
                        $image = sprintf("%s/all", $image);
                        break;
                case "pers":
                        $image = sprintf("%s/%s", $image, $GLOBALS['hostid']);
                        break;
        }
}

foreach (array("year", "month", "day") as $subdir) {
        if (isset($_REQUEST[$subdir])) {
                $image = sprintf("%s/%s", $image, $_REQUEST[$subdir]);
        }
}

$image = sprintf("%s/%s.png", $image, $_REQUEST['image']);

// 
// Send file to browser:
// 
if (file_exists($image)) {
        header(sprintf("Content-Type: %s", "image/png"));
        header(sprintf("Content-Length: %d", filesize($image)));
        readfile($image);
} else {
        error_log(sprintf("The image file %s do not exist", $image));
        exit(1);
}
?>
