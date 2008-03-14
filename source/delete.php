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
// Deletes a pending or finished job. Crashed and error job also
// counts as finished.
// 

//
// Get configuration.
// 
include "../conf/config.inc";
include "../include/common.inc";

// 
// Helper function for deleting a directory (recursive).
// 
function delete_directory($root)
{
    if(file_exists($root)) {
	$handle = opendir($root);
	if($handle) {
	    while(false !== ($file = readdir($handle))) {
		if($file != "." && $file != "..") {
		    $path = sprintf("%s/%s", $root, $file);
		    if(is_dir($path)) {
			delete_directory($path);
		    }
		    if(is_file($path) || is_link($path)) {
			unlink($path);
		    }
		}
	    }
	    closedir($handle);
	}
	else {
	    die("Failed read directory");
	}
	rmdir($root);
    }
}

// 
// Delete job directory (unqueue) for a single job.
// 
function delete_single_job($hostid, $resdir, $jobid)
{
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
	    
	    // 
	    // Recursive delete the job directory.
	    // 
	    delete_directory($resdir);
	}
    }
}

// 
// Delete a list of jobs.
// 
function delete_multiple_jobs($hostid, $filter)
{
    $jobs = get_jobs($hostid, "none", $filter);
    foreach($jobs as $jobdir => $job) {
	if($job['state'] != "running") {
	    delete_single_job($hostid, $jobdir, $job['jobid']);
	}
    }
}

// 
// The error handler.
// 
function error_handler($type)
{
    // 
    // Redirect caller back to queue.php and let it report an error.
    // 
    header("Location: queue.php?error=delete&type=$type");
}

// 
// Sanity check:
// 
if(!isset($_COOKIE['hostid'])) {
    error_handler("hostid");
}

// 
// Delete multiple jobs at once or a single job.
// 
if(isset($_REQUEST['multiple'])) {
    // 
    // Sanity check:
    // 
    if(!isset($_REQUEST['filter'])) {
	error_handler("params");
    }
    
    delete_multiple_jobs($_COOKIE['hostid'], $_REQUEST['filter']);
}
else {
    // 
    // Sanity check:
    // 
    if(!isset($_REQUEST['jobid']) || !isset($_REQUEST['result'])) {
	error_handler("params");
    }

    delete_single_job($_COOKIE['hostid'], $_REQUEST['result'], $_REQUEST['jobid']);
}

// 
// Used for proper redirect back.
// 
$sort   = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : "none";
$filter = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : "all";

header(sprintf("Location: queue.php?show=queue&sort=%s&filter=%s", $sort, $filter));

?>
