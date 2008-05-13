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
// Script for downloading job result.
// 

//
// Get configuration.
// 
include "../conf/config.inc";

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
if(!isset($_REQUEST['jobid']) || !isset($_REQUEST['result'])) {
    error_handler("params");
}
if(!isset($_COOKIE['hostid'])) {
    error_handler("hostid");
}

// 
// Get request parameters.
// 
$jobid  = $_REQUEST['jobid'];    // Job ID
$resdir = $_REQUEST['result'];   // Job result directory.

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
if(!file_exists($resdir)) {
    error_handler("resdir");
}

// 
// Now create the result zip if missing.
//
$zipdir  = "result";
$zipfile = "result.zip";

chdir($resdir);
if(!file_exists("result.zip")) {
    // 
    // Use bundled PECL zip extention if available.
    //
    if(extension_loaded("zip") && version_compare(phpversion(), "5.2.0", ">=")) {
	// 
	// This is a workaround because the PHP4 compiler will die
	// on enums (like ZipArchive::CREATE).
	// 
	$zipinc = realpath(sprintf("%s/../include/zip5.inc", dirname(__FILE__)));
	include $zipinc;
	if(!create_zipfile($zipfile, $zipdir)) {
	    error_handler("zip");
	}
    }
    else {
	// 
	// Fallback on external command.
	//
	$handle = popen(sprintf(ZIP_FILE_COMMAND, $zipfile, $zipdir), "r");
	pclose($handle);
    }
}

// 
// Make sure the archive where created:
//
if(file_exists($zipfile)) {
    //
    // Hint browser about filename to use for "save as..."
    // 
    header(sprintf("Content-Disposition: attachment; filename=\"%s\"", sprintf("result-job-%s.zip", $jobid)));
    header(sprintf("Content-Type: %s", "application/zip"));
    header(sprintf("Content-Length: %d", filesize($zipfile)));
    
    // 
    // Now send the file:
    // 
    readfile($zipfile);
}
else {
    error_handler("zip");
}

?>
