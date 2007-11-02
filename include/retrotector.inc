<?php

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
    $process = proc_open(sprintf("echo \"%s\" | batch", $cmd), $fds, $pipes, null, $env);
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
  
?>
