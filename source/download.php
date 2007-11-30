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
// Get request parameters.
// 
$jobid  = $_REQUEST['jobid'];    // Job ID
$resdir = $_REQUEST['result'];   // Job result directory.

// 
// Get hostid from cookie.
// 
$hostid = $_COOKIE['hostid'];

// 
// Sanity check:
// 
if(!isset($hostid)) {
    die("Failed get host ID. Do you have cookies enabled?");
}
if(!isset($jobid) || !isset($resdir)) {
    die("One or more required request parameters is missing or unset");
}

// 
// Build path to result directory:
// 
$resdir = sprintf("%s/jobs/%s/%s", CACHE_DIRECTORY, $hostid, $resdir);

// 
// If result directory is missing, the show an error message.
// 
if(!file_exists($resdir)) {
    die("The result directory is missing");
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
	include "../include/zip5.inc";
	create_zipfile($zipfile, $zipdir);	
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
    header(sprintf("Content-Disposition: attachment; filename=\"%s\"", sprintf("result-job-%d.zip", $jobid)));
    header(sprintf("Content-Type: %s", "application/zip"));
    header(sprintf("Content-Length: %d", filesize($zipfile)));
    
    // 
    // Now send the file:
    // 
    readfile($zipfile);
}
else {
    // 
    // Send error document (is their a better solution when zip-file fails?).
    // 
    header("HTTP/1.1 500 Internal Server Error");
    print("<html><body><h4>HTTP Error 500 - Internal Server Error</h4>Failed create zip archive. Contact the server administrator for further information.</body></html>");
    exit(1);
}

?>
