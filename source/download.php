<?php

// -------------------------------------------------------------------------------
//  Copyright (C) 2007-2011 Anders Lövgren
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
// Script for downloading job result or indata.
// 
//
// Get configuration.
// 
include "../conf/config.inc";

// 
// Get support functions:
// 
include '../include/download.inc';

// 
// The error handler.
// 
function error_handler($type)
{
        // 
        // Redirect caller back to queue.php and let it report an error.
        // 
        header("Location: queue.php?error=download&type=$type");
}

// 
// Check required parameters.
// 
if (!isset($_REQUEST['jobid']) || !isset($_REQUEST['result'])) {
        error_handler("params");
}
if (!isset($_COOKIE['hostid'])) {
        error_handler("hostid");
}

// 
// Get request parameters.
// 
$jobid = $_REQUEST['jobid'];    // Job ID
$resdir = $_REQUEST['result'];  // Job result directory.
// 
// Get hostid from cookie.
// 
$hostid = $_COOKIE['hostid'];

// 
// Build path to result directory:
// 
$resdir = sprintf("%s/jobs/%s/%s", CACHE_DIRECTORY, $hostid, $resdir);

// 
// If result directory is missing, the show an error message.
// 
if (!file_exists($resdir)) {
        error_handler("resdir");
}

if (isset($_REQUEST['what'])) {
        if ($_REQUEST['what'] == "indata") {
                download_indata($resdir);
        } else if ($_REQUEST['what'] == "result") {
                download_result($resdir, $jobid);
        }
} else {
        download_result($resdir, $jobid);
}

