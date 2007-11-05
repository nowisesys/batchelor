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
$file = sprintf("%s/stdout", $resdir);
if(!file_exists($file)) {
    die("The result file is missing.");
}

$content = file_get_contents($file);

//
// Hint browser about filename to use for "save as..."
// 
header(sprintf("Content-Disposition: attachment; filename=\"%s\"", sprintf("result-job%d.txt", $jobid)));
header(sprintf("Content-Type: %s", "text/plain"));
header(sprintf("Content-Length: %d", strlen($content)));

// 
// Now send the file:
// 
print $content;

?>
