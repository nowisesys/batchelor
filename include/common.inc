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
// Include replacements for missing file_put_contents() and
// stream_get_contents(). This is required if PHP < 5.0.0
// 
if(!function_exists("stream_get_contents") && 
   !function_exists("file_put_contents")) {
    include "missing/contents.inc";
}

// 
// Enables process (job) control thru sending signals to a running or
// stopped/paused process. This function might have side-effects, e.g. 
// sending certain signals might cause the "clear" or "flag" action to 
// be performed (see details in conf/config.inc).
// 
function process_control($hostid, $result, $jobid, $signal, &$error)
{
    global $signals;     // Defined in conf/config.inc
    
    // 
    // Build path to result directory:
    // 
    $resdir = sprintf("%s/jobs/%s/%s", CACHE_DIRECTORY, $hostid, $result);
    
    // 
    // If result directory is missing, the show an error message.
    // 
    if(!file_exists($resdir)) {
	$error = array( "where" => "resdir", "reason" => null );
	return false;
    }
    
    // 
    // Check that pid-file exists:
    // 
    $pidfile = sprintf("%s/pid", $resdir);
    if(!file_exists($pidfile)) {
	$error = array( "where" => "pid", "reason" => "file" );
	return false;
    }
    
    $sigval = $signals[$signal]['value'];
    $sigact = $signals[$signal]['action'];
    
    // 
    // Try send signal to process:
    // 
    $pid = intval(file_get_contents($pidfile));
    if(!posix_kill($pid, 0)) {
	// 
	// No process running or signaling not permitted.
	// 
	$error = array( "where" => "pid", "reason" => "proc" );
	return false;
    } else {
	if(!posix_kill($pid, $sigval)) {
	    // 
	    // Failed send signal to process.
	    // 
	    $error = array( "where" => "pid", "reason" => "perm" );
	    return false;
	}
    }
    
    // 
    // Perform additional tasks associated with sending the signal:
    // 
    switch($sigact) {
     case "clear":
	delete_single_job($hostid, $result, $jobid);
	break;
     case "flag":
	file_put_contents(sprintf("%s/signal", $resdir), $signal);
	break;
     case "none":
     default:
	break;
    }
    
    return true;
}

// 
// Runs a batch job. Returns associative array with jobid, date, 
// time and unix timestamp for started job.
// 
function run_process($cmd, $dir, &$batch)
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
    
    $running = exec(BATCH_RUNNING);
    if(!is_numeric($running)) {
	put_error("Failed get number of running jobs");
	return false;
    }
    
    if($running < BATCH_LIMIT) {
	$method = BATCH_EXECUTE;
    }
    else {
	$method = BATCH_COMMAND;
    }
    
    if(version_compare(phpversion(), "5.0.0") < 0) {
	$timezone = getenv("TZ");
	putenv(sprintf("TZ=%s", $env['TZ']));
	$process = proc_open(sprintf($method, $cmd), $fds, $pipes);
	putenv(sprintf("TZ=%s", $timezone));
    }
    else {
	$process = proc_open(sprintf($method, $cmd), $fds, $pipes, null, $env);
    }
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
	    put_error("Failed run batch job");
	    return false;
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
	    put_error(sprintf("Failed process output of batch job"));
	    return false;
	}
    }
    else {
	put_error("Failed run batch jobb $cmd");
	return false;
    }
    return true;
}

// 
// Process output from the batch (at) command. The $str variable
// contains the output from the batch command. The output *may*
// differ depending on system and locale settings.
// 
function split_batch_output($str)
{
    $rows = explode("\n", $str);    

    // 
    // We need to adjust for the current timezone:
    // 
    if(function_exists("timezone_open") && class_exists("DateTime")) {
	// 
	// These functions was included in PHP >= 5.1.0:
	// 
	$timezone = date_default_timezone_get();
	$offset   = timezone_offset_get(timezone_open($timezone), new DateTime("now"));	
    } else {
	// 
	// Fall back on the old date() function:
	// 
	$timezone = date("T");
	$offset   = date("Z");
    }
    
    foreach($rows as $row) {
	// 
	// Match 'job 20 at 2007-10-31 16:05'
	// 
	$match = array();
	if(preg_match('/job (\d+) at ([0-9]{4}-[0-9]{2}-[0-9]{2}) ([0-9]{2}:[0-9]{2})/', $row, $match)) {
	    $stamp = strtotime(sprintf("%s %s", $match[2], $match[3])) + $offset;
	    return array( "jobid" => $match[1], "date" => $match[2], "time" => $match[3], "stamp" => $stamp);
	}
	else if(preg_match('/job (\d+) at (.*)/', $row, $match)) {
	    $stamp = strtotime($match[2]) + $offset;
	    $time  = strftime("%H:%M", $stamp);
	    $date  = strftime("%G-%m-%d", $stamp);
	    return array( "jobid" => $match[1], "date" => $date, "time" => $time, "stamp" => $stamp);
	}
	else if(preg_match('/job (.*?) at (.*)/', $row, $match)) {
	    $stamp = strtotime($match[2]) + $offset;
	    $time  = strftime("%H:%M", $stamp);
	    $date  = strftime("%G-%m-%d", $stamp);
	    return array( "jobid" => $match[1], "date" => $date, "time" => $time, "stamp" => $stamp);
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
	if(!create_directory($mapdir, CACHE_PERMISSION, true)) {
	    error_exit("Failed create peer to hostid map directory");
	}
    }
    $adir = sprintf("%s/inaddr", $mapdir);
    if(!file_exists($adir)) {
	if(!create_directory($adir, CACHE_PERMISSION, true)) {
	    error_exit("Failed create peer => hostid map directory");
	}
    }
    $hdir = sprintf("%s/hostid", $mapdir);
    if(!file_exists($hdir)) {
	if(!create_directory($hdir, CACHE_PERMISSION, true)) {
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
// Returns formatted UNIX timestamp as string.
// 
function format_timestamp($stamp)
{
    return strftime(TIMESTAMP_FORMAT, intval($stamp));
}

// 
// Get humanized string (like "3 weeks") from second value.
//
function seconds_to_string($original)
{
    // 
    // Save original value to avoid error propagation.
    // 
    $current  = $original;
    $reminder = 0;
    
    $timestr  = "$current sec";
    $divide   = 60;
    $current  = floor($original / $divide);
    $reminder = floor($original % $divide);
    if($current >= 2) {
	if($reminder != 0) {
	    $timestr  = "$current min $reminder sec";
	} else {
	    $timestr  = "$current min";
	}
	$divide  *= 60;
	$current  = floor($original / $divide);
	$reminder = floor($original % $divide);
	if($current >= 2) {
	    if($reminder != 0) {
		$timestr  = "$current hours $reminder min";
	    } else {
		$timestr  = "$current hours";
	    }
	    $divide  *= 24;
	    $current  = floor($original / $divide);
	    $reminder = floor($original % $divide);
	    if($current >= 4) {
		if($reminder != 0) {		    
		    $timestr  = "$current days $reminder $hours";
		} else {
		    $timestr  = "$current days";
		}
		$divide  *= 7;
		$current  = floor($original / $divide);
		$reminder = floor($original % $divide);
		if($current >= 4) {
		    if($reminder != 0) {
			$timestr  = "$current weeks $reminder days";
		    } else {
			$timestr  = "$current weeks";
		    }
		    $divide  *= 4;
		    $current  = floor($original / $divide);
		    $reminder = floor($original % $divide);
		    if($current > 3) {
			if($reminder != 0) {
			    $timestr  = "$current months $reminder weeks";
			} else {
			    $timestr  = "$current months";
			}
			$divide  *= 12;
			$current  = floor($original / $divide);
			$reminder = floor($original % $divide);
			if($current >= 3) {
			    if($reminder != 0) {
				$timestr = "$current years $reminder months";
			    } else {
				$timestr = "$current years";
			    }
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

// 
// Count number of digits in num.
// 
function number_to_digits($num)
{
    if($num <= 0) {
	return 1;
    }
    
    $digits = 0;
    while($num >= 1) {
	$num = $num / 10;
	$digits++;
    }
    return $digits;
}

// 
// A portable mkdir() replacement.
//
function create_directory($path, $mode, $recursive)
{
    if(version_compare(phpversion(), "5.0.0") < 0) {
	// 
	// mkdir() in PHP < 5.0.0 don't handle recursive.
	// 
	$parts = explode("/", $path);
	$dir = "";
	$result = false;
	foreach($parts as $part) {
	    $dir .= $part . "/";
	    if(!file_exists($dir)) {
		$result = mkdir($dir, $mode);
		if(!$result) {
		    return $result;
		}
	    }
	}
	return $result;
    }
    else {
	return mkdir($path, $mode, $recursive);
    }
}

// 
// Set cookie so we can associate peer with submitted, running
// and finished jobs.
// 
function update_hostid_cookie()
{
    if(isset($_COOKIE['hostid'])) {
	$GLOBALS['hostid'] = $_COOKIE['hostid'];
    }
    else {
	$GLOBALS['hostid'] = md5($_SERVER['REMOTE_ADDR']);
	if(USE_SESSION_COOKIES) {
	    // 
	    // Set a session cookie.
	    // 
	    setcookie("hostid", $GLOBALS['hostid']);
	}
	else {
	    // 
	    // Set a persistent cookie.
	    // 
	    setcookie("hostid", $GLOBALS['hostid'], time() + COOKIE_LIFE_TIME);
	}
    }    
}

// 
// Prints the message box that displays i.e. error messages.
// The $type argument is either error, warning or info.
// 
function print_message_box($type, $msg)
{
    print "<div id=\"info\"><table><tr><td><img src=\"icons/nuvola/big/$type.png\"></td><td valign=\"top\">$msg</td></tr></table></div>";
}

//
// Returns true if trusted hosts is defined (see TRUSTED_HOSTS in conf/config.inc)
// and remote host is in the defined array.
//
function trusted_host($ipaddr)
{
    static $trusted = NULL;
    
    if(is_null($trusted)) {
	if(defined("TRUSTED_HOSTS")) {
	    $trusted = in_array($ipaddr, explode(",", TRUSTED_HOSTS));
	} else {
	    $trusted = false;
	}
    }
    return $trusted;
}

// 
// Put an error message to error message list.
// 
function put_error($str)
{
    $GLOBALS['error'][] = $str;
}

// 
// Get array of error messages.
// 
function get_errors()
{
    return $GLOBALS['error'];
}

// 
// Test if any error message is set.
// 
function has_errors()
{
    return isset($GLOBALS['error']);
}

// 
// Clear any errors in list.
// 
function clear_errors()
{
    $GLOBALS['error'] = null;
}

// 
// Log any errors to web server log and flush error buffer 
// if flush is true.
//
function log_errors($flush = true)
{
    if(has_errors()) {
	foreach(get_errors() as $error) {
	    error_log($error);
	}
	if($flush) {
	    clear_errors();
	}
    }
}

// 
// Get last logged error message.
// 
function get_last_error()
{
    if(has_errors()) {
	$last = count($GLOBALS['error']) - 1;			       
	return $GLOBALS['error'][$last];
    }
}

// 
// Put an warning message to warning message list. If jobdir is 
// non-null, then its appended to the file named warning in the
// job directory.
// 
function put_warning($str, $jobdir = null)
{
    $GLOBALS['warning'][] = $str;
    if($jobdir) {
	file_put_contents(sprintf("%s/warning", $jobdir), 
			  sprintf("%s\n", $str), 
			  FILE_APPEND);
    }
}

// 
// Get array of warning messages.
// 
function get_warnings()
{
    return $GLOBALS['warning'];
}

// 
// Test if any warning message is set.
// 
function has_warnings()
{
    return isset($GLOBALS['warning']);
}

// 
// Clear any warnings in list.
// 
function clear_warnings()
{
    $GLOBALS['warning'] = null;
}

// 
// Log any warnings to web server log and flush warning buffer 
// if flush is true.
//
function log_warnings($flush = true)
{
    if(has_warnings()) {
	foreach(get_warnings() as $warning) {
	    warning_log($warning);
	}
	if($flush) {
	    clear_warnings();
	}
    }
}

// 
// Get last logged warning message.
// 
function get_last_warning()
{
    if(has_warnings()) {
	$last = count($GLOBALS['warning']) - 1;			       
	return $GLOBALS['warning'][$last];
    }
}

?>
