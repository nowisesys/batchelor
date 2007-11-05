<?php

// 
// Deletes a pending or finished job. Crashed and error job also
// counts as finished.
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
// Helper function for deleting a directory.
// 
function delete_directory($path)
{
    $top = $path;
    
    $handle = opendir($path);
    if($handle) {
	while(false !== ($file = readdir($handle))) {
	    if($file != "." && $file != "..") {
		$path = sprintf("%s/%s", $top, $file);
		if(is_file($path)) {
		    unlink($path);
		}
	    }
	}
	closedir($handle);
	rmdir($top);
    }
}

// 
// Build path to result directory:
// 
$resdir = sprintf("%s/jobs/%s/%s", CACHE_DIRECTORY, $hostid, $resdir);

// 
// If result directory is missing, the silently refuse to unqueue job.
// 
if(file_exists($resdir)) {
    $jobfile = sprintf("%s/jobid", $resdir);
    if(file_exists($jobfile)) {
	if(file_get_contents($jobfile) != $jobid) {
	    die("Job ID don't match recorded Job ID");
	}
	
	// 
	// We got an existing result directory with authentic job ID under
	// the cache directory matching hostid. It should be safe to unqueue
	// the job and delete the directory.
	// 
	$handle = popen(sprintf(BATCH_REMOVE, $jobid), "r");
	pclose($handle);
	
	delete_directory($resdir);
    }
}

header("Location: index.php");

?>
