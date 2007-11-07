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
// Utility functions. The script including this file should define
// an error_exit($str) function for fatal errors.
// 

// 
// Runs a batch job. Returns associative array with jobid, date, 
// time and unix timestamp for started job.
// 
function run_process($cmd, $dir)
{
    //
    // The "pipe" key can be changed to "file":
    // 
    // $file = tempnam("/tmp", "php");
    // $fds[2] = array("file", $file, "w");
    //     
    $fds = array(
		 0 => array("pipe", "r"),
		 1 => array("pipe", "w"),
		 2 => array("pipe", "w")
		 );
    $env = array( "SHELL" => "/bin/bash", "TZ" => "UTC" );
    $pipes = array();
    
    // 
    // Run the command as a batch job.
    //
    $process = proc_open(sprintf(BATCH_COMMAND, $cmd), $fds, $pipes, null, $env);
    if(is_resource($process)) {
	// 
	// Read stdot and stderr.
	//
	$stdout = stream_get_contents($pipes[1]);
	$stderr = stream_get_contents($pipes[2]);
	
	// 
	// Close read/write pipes.
	// 
	foreach($pipes as $pipe) {
	    fclose($pipe);
	}
	$result = proc_close($process);

	// 
	// Check exit status of process.
	// 
	if($result != 0) {
	    error_exit("Failed run batch job");
	}
	
	// 
	// Check the output from the batch command. The output from batch is always
	// written to stderr.
	// 
	// Exempel output from at v3.1.8 on Linux:
	// 
	//   warning: commands will be executed using /bin/sh 
	//   job 19 at 2007-10-31 15:51
	// 
	$batch = split_batch_output($stderr);
	if(!isset($batch)) {
	    error_exit(sprintf("Failed process output of batch job"));
	}
	else {
	    // 
	    // Return job properties array.
	    // 
	    return $batch;
	}
    }
    else {
	error_exit("Failed run batch jobb $cmd");
    }
}

// 
// Process output from the batch (at) command. The $str variable
// contains the output from the batch command. The output *may*
// differ depending on system and locale settings.
// 
function split_batch_output($str)
{
    $rows = explode("\n", $str);    
    
    foreach($rows as $row) {
	// 
	// Match 'job 20 at 2007-10-31 16:05'
	// 
	$match = array();
	if(preg_match('/job (\d+) at ([0-9]{4}-[0-9]{2}-[0-9]{2}) ([0-9]{2}:[0-9]{2})/', $row, $match)) {
	    $stamp = strtotime(sprintf("%s %s", $match[2], $match[3]));
	    return array( "jobid" => $match[1], "date" => $match[2], "time" => $match[3], "stamp" => $stamp);
	}
    }
    
    return null;
}

// 
// Save hostid (auto generated md5) <=> peer mapping to file.
// 
function save_hostid_mapping($mapdir, $hostid, $peer)
{
    // 
    // Create directory if not already exists.
    // 
    if(!file_exists($mapdir)) {
	if(!mkdir($mapdir, CACHE_PERMISSION, true)) {
	    error_exit("Failed create peer to hostid map directory");
	}
    }
    $adir = sprintf("%s/inaddr", $mapdir);
    if(!file_exists($adir)) {
	if(!mkdir($adir, CACHE_PERMISSION, true)) {
	    error_exit("Failed create peer => hostid map directory");
	}
    }
    $hdir = sprintf("%s/hostid", $mapdir);
    if(!file_exists($hdir)) {
	if(!mkdir($hdir, CACHE_PERMISSION, true)) {
	    error_exit("Failed create peer => hostid map directory");
	}
    }
    
    // 
    // Save peer address <=> hostid mapping.
    // 
    $file = sprintf("%s/%s", $adir, $peer);
    if(!file_put_contents($file, $hostid)) {
	error_exit("Failed save peer => hostid map");
    }
    $file = sprintf("%s/%s", $hdir, $hostid);
    if(!file_put_contents($file, $peer)) {
	error_exit("Failed save hostid => peer address map");
    }
}

// 
// Get jobs for peer associated by the hostid variable.
// 
// The job can be in one of the following states:
// 1. Running  - no finished timestamp and jobid is in queue.
// 2. Finished - finished timestamp exists.
// 3. Crashed  - no finished timestamp and jobid is missing in queue.
// 4. Error    - finished with non-empty error output.
// 
function get_jobs($hostid)
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
	return $jobs;
    }
    
    // 
    // Loop thru all subdirectories.
    // 
    if(chdir($root)) {
	if($dirs = opendir(".")) {
	    while(false !== ($dir = readdir($dirs))) {
		if($dir != "." && $dir != "..") {
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
				    }
				}
			    }
			}
			else {
			    error_log(sprintf("Failed open result directory '%s/%s'", $root, $dir));
			}
		    }
		    else {
			error_log(sprintf("Failed change to result directory '%s/%s'", $root, $dir));
		    }
		    // 
		    // Push job onto jobs array and pop directory.
		    // 
		    $jobs[$dir] = $job;
		    chdir("..");
		}
	    }
	}
	else {
	    error_log(sprintf("Failed open cache directory '%s'", $root));
	}
	chdir($cwd);
    }
    else {
	error_log(sprintf("Failed change path to cache directory '%s'", $root));
    }
    
    // 
    // Now classify all jobs as either running, finished, error or crashed.
    // 
    $queued = get_queued_jobs();
    $result = array();
    
    foreach($jobs as $key => $job) {
	if(isset($job['finished'])) {
	    if(isset($job['stderr'])) {
		$job['state'] = "error";
	    }
	    else {
		$job['state'] = "finished";
	    }
	}
	else {
	    if(!isset($queued[$job['jobid']])) {
		$job['state'] = "crashed";
	    }
	    else {
		$job['state'] = $queued[$job['jobid']];
	    }
	}
	$result[$key] = $job;
    }
    
    return $result;
}

// 
// Return array of all pending and running jobs. The result
// array is using jobid as key and state as value.
// 
function get_queued_jobs()
{
    // 
    // The result array.
    // 
    $jobs = array();
    
    if($handle = popen(BATCH_LISTING, "r")) {
	while($str = fgets($handle)) {
	    // 
	    // Split batch jobs output. 
	    // Exempel: 
	    // '25      2007-11-05 02:54 b apache'   // pending
	    // '24      2007-11-05 02:54 = apache'   // running
	    $match = array();
	    if(preg_match("/(\d+)\s+([0-9]{4}-[0-9]{2}-[0-9]{2})\s+([0-9]{2}:[0-9]{2})\s+([b=]{1})\s+(.*)/", $str, $match)) {
		$jobs[$match[1]] = ($match[4] == "=") ? "running" : "pending";
	    }
	    else {
		error_exit("Failed parse batch queue output.");
	    }
	}
	pclose($handle);
    }
    
    return $jobs;
}

// 
// Returns formatted UNIX timestamp as string.
// 
function format_timestamp($stamp)
{
    return strftime(TIMESTAMP_FORMAT, $stamp);
}

// 
// Get humanized string (like "3 weeks") from second value.
//
function seconds_to_string($original)
{
    // 
    // Save original value to avoid error propagation.
    // 
    $current = $original;
    
    $timestr = "$current sec";
    $divide  = 60;
    $current = floor($original / $divide);
    if($current >= 2) {
	$timestr = "$current min";
	$divide *= 60;
	$current = floor($original / $divide);
	if($current >= 2) {
	    $timestr = "$current hours";
	    $divide *= 24;
	    $current = floor($original / $divide);
	    if($current >= 4) {
		$timestr = "$current days";
		$divide *= 7;
		$current = floor($original / $divide);
		if($current >= 4) {
		    $timestr = "$current weeks";
		    $divide *= 4;
		    $current = floor($original / $divide);
		    if($current > 3) {
			$timestr = "$current months";
			$divide *= 12;
			$current = floor($original / $divide);
			if($current >= 3) {
			    $timestr = "$current years";
			}
		    }
		}
	    }
	}
    }		
    
    return $timestr;
}

// 
// Get humanized string (like "5 MB") from bytes value.
// 
function bytes_to_string($original)
{
    // 
    // Save original value to avoid error propagation.
    // 
    $current = $original;
    
    $bytestr = "$current bytes";
    $divide  = 1024;
    $current = floor($original / $divide);
    if($current >= 2) {
	$bytestr = "$current kB";
	$divide *= 1024;
	$current = floor($original / $divide);
	if($current >= 2) {
	    $bytestr = "$current MB";
	    $divide *= 1024;
	    $current = floor($original / $divide);
	    if($current >= 2) {
		$bytestr = "$current GB";
	    }
	}
    }
    
    return $bytestr;
}

?>
