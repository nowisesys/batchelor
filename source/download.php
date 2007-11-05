<?php

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
    if(extension_loaded("zip") && version_compare(phpversion(), "5.2.0", ">="))  {
	// 
	// Use bundled PECL zip extention.
	//
	$zip = new ZipArchive();	 
	if($zip->open($zipfile, ZIPARCHIVE::CREATE)) {
	    $dir = opendir($zipdir);
	    if($dir) {
		while(false !== ($file = readdir($dir))) {
		    if($file != "." && $file != "..") {
			$zip->addFile(sprintf("%s/%s", $zipdir, $file));
		    }
		}
	    }
	}
	$zip->close();
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
