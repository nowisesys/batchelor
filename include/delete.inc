<?php

// -------------------------------------------------------------------------------
//  Copyright (C) 2007-2008 Anders L�vgren
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
    // Record all deleted job directories to the queue cache
    // will be properly updated.
    //
    $status = sprintf("%s/jobs/%s/status.log", CACHE_DIRECTORY, $hostid);

    // 
    // The resdir argument is actually the name of the jobdir.
    // 
    $jobdir = $resdir;
    
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
	else if($jobid == 0) {
	    // 
	    // This is a job directory crashed due to missconfiguration.
	    // 
	    delete_directory($resdir);
	}
    }
    
    // 
    // Append deleted job to status log:
    // 
    file_put_contents($status, sprintf("%s\n", $jobdir), FILE_APPEND);
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

?>