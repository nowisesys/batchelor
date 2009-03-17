<?php

// -------------------------------------------------------------------------------
//  Copyright (C) 2007-2008 Anders Lövgren
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
// This file contains functions related to the job queue. These functions
// are called both from source/queue.php and from the web service common
// library in include/ws.inc that is used by the web service interfaces
// under source/ws/xxx.
// 

//
// This callback is used by get_jobs() to sort the jobs array based
// on value of global variable 'sort' (request paramter).
// 
function sort_jobs($a, $b)
{    
    // 
    // If jobs is pending then we can't sort on started i.e.
    // 
    if(isset($GLOBALS['sort'])) {
	if(!isset($a[$GLOBALS['sort']])) {
	    return true;
	}
	if(!isset($b[$GLOBALS['sort']])) {
	    return false;
	}	
    }
    
    if(isset($GLOBALS['order']) && $GLOBALS['order'] == "desc") {
	return $a[$GLOBALS['sort']] < $b[$GLOBALS['sort']];
    }
    else {
	return $a[$GLOBALS['sort']] > $b[$GLOBALS['sort']];
    }
}

// 
// This callback is used by get_jobs() to filter out values based
// on value of the global variable 'filter' (request parameter).
//
function filter_jobs($a)
{
    if($GLOBALS['filter'] == "waiting") {
	return $a['state'] == "pending" || $a['state'] == "running";
    } 
    return $a['state'] == $GLOBALS['filter'];
}

// 
// Get jobs for peer associated by the hostid variable.
// 
// The job can be in one of the following states:
// 1. Running  - no finished timestamp and jobid is in queue.
// 2. Finished - finished timestamp exists.
// 3. Crashed  - no finished timestamp and jobid is missing in queue.
// 4. Warning  - finished with non-empty error output.
// 5. Error    - same as Warning, but also has an fatal exit message file.
// 
// The $sort parameter defines the ordering between elements in result
// array. Possible values for $sort is none, started, jobid or state. 
// The sorting can be further finetuned by defining the global variable 
// order to either asc or desc.
// 
// The $filter parameter defines which elements should be inserted
// in the result array. Possible values are all, pending, running, finished, 
// warning, error or crashed.
// 
function get_jobs($hostid, $sort = "none", $filter = "all")
{
    // 
    // The result array.
    // 
    $jobs = array();

    // 
    // Save current working directory.
    // 
    $cwd = getcwd();

    // 
    // Build path to directory containing all job directories for this hostid.
    // 
    $root = sprintf("%s/jobs/%s", CACHE_DIRECTORY, $hostid);
    
    // 
    // Prevent error when updating job list without any submitted job.
    // 
    if(!file_exists($root)) {
	put_error("The root directory don't exists");
	return $jobs;
    }

    // 
    // Loop thru all subdirectories.
    // 
    if(chdir($root)) {
	// 
	// Read serialized queue data if it exist. Then look if status update
	// log exists. If serialized queue exist, but no status update then we
	// are done. If serialized queue exist and we got status updates, then
	// only process these directories. If no serialized queue or status
	// update exist, the process all sub directories.
	//
	if(file_exists("queue.ser")) {
	    $jobs = unserialize(file_get_contents("queue.ser"));
	}
	$update = null;
	if(file_exists("status.log")) {
	    $update = explode("\n", file_get_contents("status.log"));
	    if(!unlink("status.log")) {
	    	put_error("Failed unlink status.log");
	    }
	}
	if(count($jobs) == 0 || isset($update)) {
	    if(isset($update)) {
		// 
		// Process deleted job directories:
		// 
		foreach($update as $dir) {
		    if(!file_exists($dir) && isset($jobs[$dir])) {
			unset($jobs[$dir]);
		    }
		}
	    }
			
	    if($dirs = opendir(".")) {
		while(false !== ($dir = readdir($dirs))) {
		    if($dir != "." && $dir != "..") {
			if(is_dir($dir)) {
			    // 
			    // Only process job directories thats in the update
			    // array, all others have to status change.
			    // 
			    if(isset($update)) {
				if(!in_array($dir, $update)) {
				    continue;      // no status change
				}
			    }
			    if(chdir($dir)) {
				if($files = opendir(".")) {
				    $job = array();
				    while(false !== ($file = readdir($files))) {
					if($file != "." && $file != "..") {
					    // 
					    // Process all files inside $root/$dir
					    //
					    switch($file) {
					     case "indata":
						// ignore
						break;
					     case "jobid":
					     case "queued":
					     case "started":
					     case "finished":
						$job[$file] = trim(file_get_contents($file));
						break;
					     case "stdout":
					     case "stderr":
						if(filesize($file)) {
						    $job[$file] = filemtime($file);   // save timestamp if filesize > 0 bytes
						}
						break;
					     case "fatal":
						$job[$file] = filemtime($file);
						break;
					     case "name":
						if(defined("QUEUE_SHOW_NAMES") && QUEUE_SHOW_NAMES) {
						    $name = trim(file_get_contents($file));
						    if(strlen($name) > QUEUE_MAX_NAME_LENGHT) {
							if(QUEUE_TRUNCATE_NAME == "head") {
							    $part = QUEUE_MAX_NAME_LENGHT - 3;
							    $name = sprintf("...%s", substr($name, -$part, $part));
							}
							else if(QUEUE_TRUNCATE_NAME == "middle") {
							    $part = QUEUE_MAX_NAME_LENGHT / 2 - 1;
							    $name = sprintf("%s...%s", 
									    substr($name, 0, $part), 
									    substr($name, -$part, $part));
							} else if(QUEUE_TRUNCATE_NAME == "tail") {
							    $name = sprintf("%s...", substr($name, 0, QUEUE_MAX_NAME_LENGHT - 3));
							}
						    }
						    $job[$file] = $name;
						}
						break;
					     case "warning":
						$job[$file] = true;
						break;
					    }
					}
				    }
				}
				else {
				    put_error(sprintf("Failed open result directory '%s/%s'", $root, $dir));
				}
			    }
			    else {
				put_error(sprintf("Failed change to result directory '%s/%s'", $root, $dir));
			    }
			    // 
			    // Push job onto jobs array and pop directory.
			    // 
			    $jobs[$dir] = $job;
			    chdir("..");
			}
		    }
		}
	    }
	    else {
		put_error(sprintf("Failed open cache directory '%s'", $root));
		return false;
	    }
	    // 
	    // Queue has been modified.
	    // 
	    file_put_contents("queue.ser", serialize($jobs));
	}
	chdir($cwd);
    }
    else {
	put_error(sprintf("Failed change path to cache directory '%s'", $root));
	return false;
    }
    
    // 
    // Now classify all jobs as either running, finished, warning, error or crashed.
    // 
    $result = array();
    $queued = array();
    if(!get_queued_jobs($queued)) {
	return false;
    }
    
    foreach($jobs as $key => $job) {
	if(isset($job['finished'])) {
	    if(isset($job['stderr'])) {
		if(isset($job['fatal'])) {
		    $job['state'] = "error";
		}
		else {
		    $job['state'] = "warning";
		}
	    }
	    else {
		$job['state'] = "finished";
	    }
	}
	else {
	    if(isset($job['jobid'])) {
		if(!isset($queued[$job['jobid']])) {
		    if(defined("BATCH_START_DEQUEUES") && BATCH_START_DEQUEUES == 1) {
		        $job['state'] = "running";
		    } else {
		        $job['state'] = "crashed";
		    }
		}
		else {
		    $job['state'] = $queued[$job['jobid']];
		}
	    }
	    else {
		// 
		// No jobid (0)? This must be a crashed job.
		// 
		$job['state'] = "crashed";
	    }
	}
	$result[$key] = $job;
    }

    // 
    // Apply filter if requested.
    // 
    if($filter != "all") {
	$GLOBALS['filter'] = $filter;
	$result = array_filter($result, "filter_jobs");
    }
    
    // 
    // Apply sorting if requested.
    // 
    if($sort != "none") {
	$GLOBALS['sort'] = $sort;
	uasort($result, "sort_jobs");
    }
    
    return $result;
}

// 
// Return array of all pending and running jobs. The result
// array is using jobid as key and state as value.
// 
function get_queued_jobs(&$jobs)
{
    // 
    // The result array.
    // 
    $jobs = array();

    if($handle = popen(BATCH_LISTING, "r")) {
	while($str = fgets($handle)) {
		
	    // 
	    // Split batch jobs output. The output format differs between Linux, BDS/Mac OS X
	    // and Solaris 8 (and later?).
	    // 
	    
	    $match = array();
	    
	    // 
	    // Linux format: 
	    //   '25      2007-11-05 02:54 b apache'   // pending
	    //   '24      2007-11-05 02:54 = apache'   // running
	    // 
	    if(preg_match("/(\d+)\s+([0-9]{4}-[0-9]{2}-[0-9]{2})\s+([0-9]{2}:[0-9]{2})\s+([b=]{1})\s+(.*)/", $str, $match)) {
		$jobs[$match[1]] = ($match[4] == "=") ? "running" : "pending";
	    }
	    // 
	    // Solaris 8 (and later?) format:
	    //   'user = root     1210583160.a    Mon May 12 11:06:00 2008'
	    //   'user = root     1210583160.a    Mon May 12 11:06:00 2008'
	    // 
	    // NOTE: Listing as non-root user don't show user = xxx.
	    // 	    
	    else if(preg_match('/([0-9]*\.[ab]{1})\t(.*)/', $str, $match)) {
		$jobs[$match[1]] = "pending";
	    }
	    // 
	    // Mac OS X format:
	    //   '1       Thu Mar  6 09:38:00 2008'
	    //   '2       Thu Mar  6 09:39:00 2008'
	    // 
	    // NOTE: Simply flag job as pending because information is missing.
	    // 
	    else if(preg_match("/(\d+)\s+(.*)/", $str, $match)) { 
		$jobs[$match[1]] = "pending";
	    } 
	    // 
	    // No regexp matched. We better give up and let someone fix this.
	    // 
	    else {
	        put_error("Failed parse batch queue output.");
		return false;
	    }
	}
	pclose($handle);
    } else {
        put_error("Failed read jobs in batch queue.");
	return false;
    }		   

    return true;
}

// 
// This function should be called to enqueue a new job. On failure it should call 
// put_error() to set a global error message and then return false. 
// 
// The $formdata parameter should be set to submitted data (or indata payload if 
// called from XML-RPC code). If its unset, then its assumed that a file was
// uploaded instead. Data for the newly enqueued job(s) are stored in the $jobs 
// variable (might contain multiple jobs).
// 
function enqueue_job($formdata, &$jobs)
{
    // 
    // Create output and job spool directories.
    // 
    $jobdir = sprintf("%s/jobs/%s", CACHE_DIRECTORY, $GLOBALS["hostid"]);
    if(!file_exists($jobdir)) {
	if(!create_directory($jobdir, CACHE_PERMISSION, true)) {
	    put_error("Failed create output directory");
	    return false;
	}
    }
    
    $jobdir = sprintf("%s/%d", $jobdir, time());
    if(!create_directory($jobdir, CACHE_PERMISSION, true)) {
	put_error("Failed create output directory");
	return false;
    }
    
    // 
    // Save peer <=> hostid mapping?
    //
    if(SAVE_HOSTID_MAPPING) {
	$mapdir = sprintf("%s/map", CACHE_DIRECTORY);
	save_hostid_mapping($mapdir, $GLOBALS['hostid'], $_SERVER['REMOTE_ADDR']);
    }
    
    // 
    // Create path to indata file.
    // 
    if(UPLOAD_PRESERVE_FILENAME && isset($_FILES['file']['name'])) {
	$indata = sprintf("%s/%s", $jobdir, $_FILES['file']['name']);
    }
    else {
	$indata = sprintf("%s/indata", $jobdir);
    }
        
    // 
    // Process request parameters.
    // 
    if(isset($formdata)) {
	// 
	// Save the data to file.
	// 
	if(!file_put_contents($indata, $formdata)) {
	    if(!cleanup_jobdir($jobdir, $indata)) {
		return false;
	    }
	    if(strlen($formdata) == 0) {
		put_error("No job data was submitted");
		return false;
	    }
	    else {
		put_error("Failed save data to file");
		return false;
	    }
	}
    }
    else {
	// 
	// Make sure the uploaded file is posted file and not an
	// system file, i.e. /etc/passwd
	// 
	if(is_uploaded_file($_FILES['file']['tmp_name'])) {
	    if(!rename($_FILES['file']['tmp_name'], $indata)) {
		if(!cleanup_jobdir($jobdir, $_FILES['file']['tmp_name'])) {
		    return false;
		}
		put_error("Failed move uploaded file");
		return false;
	    }
	    if(UPLOAD_PRESERVE_FILENAME) {
		$symlink = sprintf("%s/indata", $jobdir);
		if(!file_exists($symlink)) {
		    if(!symlink($indata, $symlink)) {
			put_error("Failed symlink uploaded file");
			return false;
		    }
		}
	    }
	}
	else {
	    rmdir($jobdir);
	    if(isset($_FILES['file']['error'])) {
		switch($_FILES['file']['error']) {
		 case UPLOAD_ERR_INI_SIZE:
		    // 
		    // Value: 1; The uploaded file exceeds the upload_max_filesize directive in php.ini. 
		    //
		    put_error("The uploaded file exceeds PHP's maximum allowed filesize");
		    return false;
		    break;
		 case UPLOAD_ERR_FORM_SIZE:
		    // 
		    // Value: 2; The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form. 
		    //
		    put_error("The uploaded file exceeds the maximum allowed filesize.");
		    return false;
		    break;
		 case UPLOAD_ERR_PARTIAL:
		    //
		    // Value: 3; The uploaded file was only partially uploaded. 
		    //
		    put_error("The uploaded file was only partially uploaded");
		    return false;
		    break;
		 case UPLOAD_ERR_NO_FILE:
		    //
		    // Value: 4; No file was uploaded. 
		    // 
		    put_error("No file was uploaded");
		    return false;
		    break;
		 case UPLOAD_ERR_NO_TMP_DIR:
		    // 
		    // Value: 6; Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3. 
		    // 
		    put_error(sprintf("Missing a temporary folder, contact %s", CONTACT_STRING));
		    return false;
		    break;
		 case UPLOAD_ERR_CANT_WRITE:
		    // 
		    // Value: 7; Failed to write file to disk. Introduced in PHP 5.1.0. 
		    //
		    put_error(sprintf("Failed to write file to disk, contact %s", CONTACT_STRING));
		    return false;
		    break;
		 case UPLOAD_ERR_EXTENSION:
		    // 
		    // Value: 8; File upload stopped by extension. Introduced in PHP 5.2.0.
		    //
		    put_error("File upload stopped by extension");
		    return false;
		    break;
		}
	    }
	    else {
		put_error("No uploaded file");
		return false;
	    }
	}
    }
    
    // 
    // The filesize test on uploaded data applies to both HTTP uploaded file
    // and data saved from request parameter data. Both gets saved to file
    // pointed to by $indata.
    // 
    if(filesize($indata) < UPLOAD_MIN_FILESIZE) {
	if(!cleanup_jobdir($jobdir, $indata)) {
	    return false;
	}
	put_error(sprintf("Uploaded file is too small (requires filesize >= %s)", 
			   bytes_to_string(UPLOAD_MIN_FILESIZE)));
	return false;
    }
    if(UPLOAD_MAX_FILESIZE != 0 && filesize($indata) > UPLOAD_MAX_FILESIZE) {
	if(!cleanup_jobdir($jobdir, $indata)) {
	    return false;
	}
	put_error(sprintf("Uploaded file is too big (accepts filesize < %s)", 
			   bytes_to_string(UPLOAD_MAX_FILESIZE)));
	return false;
    }

    //
    // Call pre enqueue hook if function is defined.
    // 
    $handled = false;
    if(function_exists("pre_enqueue_hook")) {
	if(!pre_enqueue_hook($indata, $jobdir, $handled, $jobs)) {
	    if(!cleanup_jobdir($jobdir, $indata)) {
		return false;
	    }
	    return false;
	}
	if($handled) {
	    if(!cleanup_jobdir($jobdir)) {
		return false;
	    }
	}
    }
    
    if(!$handled) {
	$job = array();
	if(!enqueue_single_job($indata, $jobdir, $job)) {
	    return false;
	}
	$jobs[] = $job;
    }
    return true;
}

// 
// This function will enqueue a single job.
// 
function enqueue_single_job($indata, &$jobdir, &$job, $index = 0)
{
    // 
    // Create new job directory if enqueuing multiple jobs from a single 
    // submit. This might be the case if enqueue_single_job() where called 
    // from pre_enqueue_hook().
    // 
    if($index != 0) {
	if($index > UPLOAD_SUBJOB_LIMIT) {
	    put_error(sprintf("Limit of maximum %d subjobs reached, no further jobs will be enqueued", UPLOAD_SUBJOB_LIMIT));
	    return false;
	}
	
	$fmt = sprintf("%%s/%%d%%0%dd", number_to_digits(UPLOAD_SUBJOB_LIMIT));
	
	$jobdir = sprintf($fmt, dirname($jobdir), time(), $index);
	if(!create_directory($jobdir, CACHE_PERMISSION, true)) {
	    put_error("Failed create output directory");
	    return false;
	}
	$subdata = "";
	if(UPLOAD_PRESERVE_FILENAME) {
	    $subdata = sprintf("%s/%s", $jobdir, basename($indata));
	} else {
	    $subdata = sprintf("%s/indata", $jobdir);
	}
	if(!rename($indata, $subdata)) {
	    put_error("Failed move indata to output directory");
	    return false;
	}
	$indata = $subdata;
    }
    
    // 
    // Make sure indata is linked as indata.
    // 
    if(basename($indata) != "indata") {
	$symlink = sprintf("%s/indata", $jobdir);
	if(!file_exists($symlink)) {
	    if(!symlink($indata, $symlink)) {
		put_error("Failed symlink uploaded file");
		return false;
	    }
	}
    }
    
    // 
    // File uploaded or created. Now we just has to start the batch
    // job. The path to the wrapper script path must be absolute.
    // 
    $resdir = sprintf("%s/result", $jobdir);
    if(!create_directory($resdir, CACHE_PERMISSION, true)) {
	if(!cleanup_jobdir($jobdir, $indata)) {
	    return false;
	}
	put_error("Failed create result directory");
	return false;
    }
    $script = realpath(dirname(__FILE__) . "/../utils/script.sh");
    $command = sprintf("%s %s %s %s", $script, $jobdir, $indata, $resdir);
    if(!run_process($command, $jobdir, $job)) { 
	return false;
    }
    
    // 
    // Save jobid and queued time to file in result dir.
    // 
    if(!file_put_contents(sprintf("%s/jobid", $jobdir), $job['jobid'])) {
	put_error("Failed save jobid");
	return false;
    }
    if(!file_put_contents(sprintf("%s/queued", $jobdir), time())) {
	put_error("Failed save job enqueue time");
	return false;
    }

    //
    // Call post enqueue hook if function is defined.
    // 
    if(function_exists("post_enqueue_hook")) {
	post_enqueue_hook($indata, $jobdir);
    }
        
    // 
    // Append job to status update log.
    // 
    file_put_contents(sprintf("%s/status.log", dirname($jobdir)),
		      sprintf("%s\n", basename($jobdir)), FILE_APPEND);
    
    return true;
}

// 
// This function should be called prior to error_exit() to
// clean the job directory on failure.
// 
function cleanup_jobdir($root, $indata = null)
{
    if(isset($indata)) {
	if(file_exists($indata)) {
	    if(!unlink($indata)) {
		put_error("Failed cleanup job directory");
		return false;
	    }
	}
    }
    
    if(file_exists($root)) {
	$handle = opendir($root);
	if($handle) {
	    while(false !== ($file = readdir($handle))) {
		if($file != "." && $file != "..") {
		    $path = sprintf("%s/%s", $root, $file);
		    if(is_dir($path)) {
			cleanup_jobdir($path);
		    }
		    if(is_file($path) || is_link($path)) {
			unlink($path);
		    }
		}
	    }
	    closedir($handle);
	}
	else {
	    put_error("Failed read job directory");
	    return false;
	}
	rmdir($root);
    }
    return true;
}

?>
