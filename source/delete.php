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
// Helper function for deleting a directory.
// 
function delete_directory($path)
{
    if(file_exists($path)) {
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
	    
	    delete_directory(sprintf("%s/result", $resdir));
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
// Sanity check:
// 
if(!isset($_COOKIE['hostid'])) {
    die("Failed get host ID. Do you have cookies enabled?");
}

// 
// Delete multiple jobs at once or a single job.
// 
if(isset($_REQUEST['multiple'])) {
    // 
    // Sanity check:
    // 
    if(!isset($_REQUEST['filter'])) {
	die("One or more required request parameters is missing or unset");
    }
    
    delete_multiple_jobs($_COOKIE['hostid'], $_REQUEST['filter']);
}
else {
    // 
    // Sanity check:
    // 
    if(!isset($_REQUEST['jobid']) || !isset($_REQUEST['result'])) {
	die("One or more required request parameters is missing or unset");
    }

    delete_single_job($_COOKIE['hostid'], $_REQUEST['result'], $_REQUEST['jobid']);
}

// 
// Used for proper redirect back.
// 
$sort   = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : "none";
$filter = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : "all";

header(sprintf("Location: index.php?show=queue&sort=%s&filter=%s", $sort, $filter));

?>
