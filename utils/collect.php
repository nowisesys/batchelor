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
// Collect statistics from runned jobs. This script should be runned either
// from the command line or as a cron job.
// 
// The statistics is collected in a directory structure organized as:
// 
// cache/
//   +-- ...
//  ...
//   +-- stats/                           // root of statistics
//        +-- date/                       // by date statistics
//             +-- summary.dat            // summary of 2007, 2008, ... (text)
//             +-- summary.png            // summary of 2007, 2008, ... (image)
//             +-- 2007/
//             +-- 2008/
//                  +-- summary.dat       // summary of 01, 02, ..., 12 (text)
//                  +-- summary.png       // summary of 01, 02, ..., 12 (image)
//                  +-- 01/               // statistics for january (month 01)
//                  +-- 02/               // statistics for february (month 02)
//                 ...
//                  +-- 12/               // statistics for december (month 12)
//                       +-- summary.dat  // summary of december (text)
//                       +-- summary.png  // summary of december (image)
//                       +-- hostxx.dat   // december statistics for hostxx (hostid)
//        +-- hostid/                     // by hostid statistics
//        +-- misc/                       // misc statistics used by collect 
//                                        // hooks (user defined functions)
// 

//
// The script should only be run in CLI mode.
//
if(isset($_SERVER['SERVER_ADDR'])) {
    die("This script should be runned in CLI mode.\n");
}

include "../conf/config.inc";
include "../include/common.inc";
include "../include/getopt.inc";

define ("LIST_QUEUE_ONCE", 0);
define ("LIST_QUEUE_PER_HOSTID", 1);
define ("LIST_QUEUE_PER_JOBDIR", 2);

//
// Show basic usage.
//
function collect_usage($prog, $sect)
{    
    print "$prog - collect statistics tool\n";
    print "\n";      
    print "Usage: $prog options...\n";
    print "Options:\n";
    print "\n";    
    print "  Standard options:\n";
    print "    -f,--force:      Recollect already collected statistics.\n";
    print "    -q,--qmode=val:  Set queue list mode (0-2, default 0)\n";
    print "    -d,--debug:      Enable debug.\n";
    print "    -v,--verbose:    Be more verbose.\n";
    print "    -h,--help:       This help.\n";
    print "    -V,--version:    Show version info.\n";
    print "\n";
    print "Notes:\n";
    print "  1. The --qmode value let users trade accuracy against processing time\n";
    print "     by defining how often the list of enqueued jobs should be fetched.\n";
    print "     Possible values are: 0 == once, 1 == per hostid, 2 == per jobdir.\n";
    print "     Lower values gives better performance at the slight chance of getting\n";
    print "     finished jobs classified as pending/running (thus ignored).\n";
}

//
// Show verison info.
//
function collect_version($prog, $vers)
{
    print "$prog - collect statistics tool ($vers)\n";
}

// 
// Check $val argument for option $key.
// 
function check_arg($key, $val, $required, $prog)
{
    if($required) {
	if(!isset($val)) {
	    die(sprintf("%s: option '%s' requires an argument\n", $prog, $key));
	}
    }
    else {
	if(isset($val)) {
	    die(sprintf("%s: option '%s' do not take an argument\n", $prog, $key));
	}	
    }
}

// 
// Parse command line options.
// 
function parse_options(&$argv, $argc, &$options)
{
    // 
    // Get command line options.
    // 
    $args = array();
    get_opt($argv, $argc, $args);
    foreach($args as $key => $val) {
    	switch($key) {
	 case "-f":
	 case "--force":
	    check_arg($key, $val, false, $options->prog);
	    $options->force = 1;
	    break;
	 case "-q":
	 case "--qmode":
	    check_arg($key, $val, true, $options->prog);
	    if($val > 2 || $val < 0) {
		die(sprintf("%s: value for '%s' is out of range, see --help\n", 
			    $options->prog, $key));
	    }
	    $options->qmode = $val;
	    break;
	 case "-d":
	 case "--debug":           // Enable debug.
	    check_arg($key, $val, false, $options->prog);
	    $options->debug = true;
	    break;
	 case "-v":
	 case "--verbose":         // Be more verbose.
	    check_arg($key, $val, false, $options->prog);
	    $options->verbose++;
	    break;
	 case "-h":
	 case "--help":            // Show help.
	    collect_usage($options->prog, $val);
	    exit(0);
	 case "-V":
	 case "--version":         // Show version info.
	    check_arg($key, $val, false, $options->prog);
	    collect_version($options->prog, $options->version);
	    exit(0);	      
	 default:
	    die(sprintf("%s: unknown option '%s', see --help\n", $options->prog, $key));
	}
    }	      
}

// 
// Get file content or return $default if not exists.
//
function collect_file_content($filename, $default)
{
    if(file_exists($filename)) {
	return file_get_contents($filename);
    }
    return $default;
}

// 
// Get state of the job.
// 
function collect_job_state($jobdir, $jobqueue)
{
    // 
    // Check state of finished job:
    // 
    if(file_exists(sprintf("%s/finished", $jobdir))) {
	$stderr = sprintf("%s/stderr", $jobdir);
	if(file_exists($stderr) && filesize($stderr) > 0) {
	    if(file_exists(sprintf("%s/fatal", $jobdir))) {
		return "error";
	    }
	    else {
		return "warning";
	    }
	}
	else {
	    return "success";
	}
    }

    // 
    // The job is either pending, running or crashed:
    // 
    $jobid = collect_file_content(sprintf("%s/jobid", $jobdir), 0);
    if(!isset($jobqueue[$jobid])) {
	return "crashed";
    }
    else {
	return $jobqueue[$jobid];
    }
}

// 
// Update submit count.
// 
function collect_submit_count($hostid, &$data, $year, $month, $day)
{
    // 
    // Submit total:
    // 
    if(!isset($data[$hostid]['submit']['count'])) {
	$data[$hostid]['submit']['count'] = 0;
    }
    $data[$hostid]['submit']['count']++;
    
    // 
    // Submit by year:
    // 
    if(!isset($data[$hostid][$year]['submit']['count'])) {
	$data[$hostid][$year]['submit']['count'] = 0;
    }
    $data[$hostid][$year]['submit']['count']++;

    // 
    // Submit by month:
    // 
    if(!isset($data[$hostid][$year][$month]['submit']['count'])) {
	$data[$hostid][$year][$month]['submit']['count'] = 0;
    }
    $data[$hostid][$year][$month]['submit']['count']++;

    // 
    // Submit by day:
    // 
    if(!isset($data[$hostid][$year][$month][$day]['submit']['count'])) {
	$data[$hostid][$year][$month][$day]['submit']['count'] = 0;
    }
    $data[$hostid][$year][$month][$day]['submit']['count']++;    
}

// 
// Update state count.
// 
function collect_state_count($hostid, &$data, $state)
{
    if(!isset($data[$hostid]['state'][$state])) {
	$data[$hostid]['state'][$state] = 0;
    }
    $data[$hostid]['state'][$state]++;
}

// 
// Helper function for counting floating avarage (arithmetric mean value):
// fn(x) = 1/n * ((n - 1) * x(n) + x(n + 1)), x >= 1
// 
function floating_mean_value($count, $acc, $last)
{
    return $acc * (($count - 1) / $count) + $last / $count;
}

// 
// Update process accounting.
// 
function collect_process_accounting($hostid, &$data, $queued, $started, $finished, $year, $month, $day)
{
    $waiting = $started - $queued;
    $running = $finished - $started;
    $process = $waiting + $running;
    
    // 
    // Process accounting total:
    // 
    if(!isset($data[$hostid]['proctime'])) {
	$data[$hostid]['proctime']['waiting'] = 0;      // mean value of queued time
	$data[$hostid]['proctime']['running'] = 0;      // mean value of execution time
	$data[$hostid]['proctime']['count'] = 0;        // number of jobs
	$data[$hostid]['proctime']['min'] = $process;   // minimum time from submit to finished
	$data[$hostid]['proctime']['max'] = 0;          // maximum time from submit to finished
    }
    $data[$hostid]['proctime']['count']++;
    $data[$hostid]['proctime']['waiting'] = floating_mean_value($data[$hostid]['proctime']['count'],
								$data[$hostid]['proctime']['waiting'], $waiting);
    $data[$hostid]['proctime']['running'] = floating_mean_value($data[$hostid]['proctime']['count'],
								$data[$hostid]['proctime']['running'], $running);
    if($process < $data[$hostid]['proctime']['min']) {
	$data[$hostid]['proctime']['min'] = $process;
    }
    if($process > $data[$hostid]['proctime']['max']) {
	$data[$hostid]['proctime']['max'] = $process;
    }
    
    // 
    // Process accounting by year:
    // 
    if(!isset($data[$hostid][$year]['proctime'])) {
	$data[$hostid][$year]['proctime']['waiting'] = 0;      // mean value of queued time
	$data[$hostid][$year]['proctime']['running'] = 0;      // mean value of execution time
	$data[$hostid][$year]['proctime']['count'] = 0;        // number of jobs
	$data[$hostid][$year]['proctime']['min'] = $process;   // minimum time from submit to finished
	$data[$hostid][$year]['proctime']['max'] = 0;          // maximum time from submit to finished
    }
    $data[$hostid][$year]['proctime']['count']++;
    $data[$hostid][$year]['proctime']['waiting'] = floating_mean_value($data[$hostid][$year]['proctime']['count'],
								$data[$hostid][$year]['proctime']['waiting'], $waiting);
    $data[$hostid][$year]['proctime']['running'] = floating_mean_value($data[$hostid][$year]['proctime']['count'],
								$data[$hostid][$year]['proctime']['running'], $running);
    if($process < $data[$hostid][$year]['proctime']['min']) {
	$data[$hostid][$year]['proctime']['min'] = $process;
    }
    if($process > $data[$hostid][$year]['proctime']['max']) {
	$data[$hostid][$year]['proctime']['max'] = $process;
    }

    // 
    // Process accounting by month:
    // 
    if(!isset($data[$hostid][$year][$month]['proctime'])) {
	$data[$hostid][$year][$month]['proctime']['waiting'] = 0;      // mean value of queued time
	$data[$hostid][$year][$month]['proctime']['running'] = 0;      // mean value of execution time
	$data[$hostid][$year][$month]['proctime']['count'] = 0;        // number of jobs
	$data[$hostid][$year][$month]['proctime']['min'] = $process;   // minimum time from submit to finished
	$data[$hostid][$year][$month]['proctime']['max'] = 0;          // maximum time from submit to finished
    }
    $data[$hostid][$year][$month]['proctime']['count']++;
    $data[$hostid][$year][$month]['proctime']['waiting'] = floating_mean_value($data[$hostid][$year][$month]['proctime']['count'],
								$data[$hostid][$year][$month]['proctime']['waiting'], $waiting);
    $data[$hostid][$year][$month]['proctime']['running'] = floating_mean_value($data[$hostid][$year][$month]['proctime']['count'],
								$data[$hostid][$year][$month]['proctime']['running'], $running);
    if($process < $data[$hostid][$year][$month]['proctime']['min']) {
	$data[$hostid][$year][$month]['proctime']['min'] = $process;
    }
    if($process > $data[$hostid][$year][$month]['proctime']['max']) {
	$data[$hostid][$year][$month]['proctime']['max'] = $process;
    }

    // 
    // Process accounting by day:
    // 
    if(!isset($data[$hostid][$year][$month][$day]['proctime'])) {
	$data[$hostid][$year][$month][$day]['proctime']['waiting'] = 0;      // mean value of queued time
	$data[$hostid][$year][$month][$day]['proctime']['running'] = 0;      // mean value of execution time
	$data[$hostid][$year][$month][$day]['proctime']['count'] = 0;        // number of jobs
	$data[$hostid][$year][$month][$day]['proctime']['min'] = $process;   // minimum time from submit to finished
	$data[$hostid][$year][$month][$day]['proctime']['max'] = 0;          // maximum time from submit to finished
    }
    $data[$hostid][$year][$month][$day]['proctime']['count']++;
    $data[$hostid][$year][$month][$day]['proctime']['waiting'] = floating_mean_value($data[$hostid][$year][$month][$day]['proctime']['count'],
								$data[$hostid][$year][$month][$day]['proctime']['waiting'], $waiting);
    $data[$hostid][$year][$month][$day]['proctime']['running'] = floating_mean_value($data[$hostid][$year][$month][$day]['proctime']['count'],
								$data[$hostid][$year][$month][$day]['proctime']['running'], $running);
    if($process < $data[$hostid][$year][$month][$day]['proctime']['min']) {
	$data[$hostid][$year][$month][$day]['proctime']['min'] = $process;
    }
    if($process > $data[$hostid][$year][$month][$day]['proctime']['max']) {
	$data[$hostid][$year][$month][$day]['proctime']['max'] = $process;
    }
}

// 
// Collect statistics from subdirectories under hostid root directory.
// $hostid:   the hostid to collect statistics from.
// $statdir:  the statistics directory root path.
// $options:  program options.
// $data:     statistics data array.
// $jobqueue: array of queued jobs (from batch command)
// 
function collect_hostid_data($hostid, $statdir, $options, &$data, &$jobqueue)
{
    $hiddir = sprintf("%s/jobs/%s", CACHE_DIRECTORY, $hostid);      // hostid directory
    
    // 
    // See if list of pending or running jobs should be
    // updated for each hostid directory.
    // 
    if($options->qmode == LIST_QUEUE_PER_HOSTID) {
	$jobqueue = get_queued_jobs();
    }
	
    $handle = @opendir($hiddir);
    if($handle) {
	if($options->debug) {
	    printf("debug: processing job directories for host ID %s\n", $hostid);
	}
	while(($file = readdir($handle)) !== false) {
	    if($file != "." && $file != "..") {
		$jobdir = sprintf("%s/%s", $hiddir, $file);		
		// 
		// Should this directory be collected?
		// 
		$collected = sprintf("%s/collected", $jobdir);
		if(!$options->force && file_exists($collected)) {
		    if($options->debug) {
			printf("debug: directory %s already collected (skipped)\n", $file);
		    }
		    continue;
		}
		if($options->debug) {
		    printf("debug: processing job directory %s\n", $file);
		}

		// 
		// Fetch queued jobs in each loop?
		// 
		if($options->qmode == LIST_QUEUE_PER_JOBDIR) {
		    $jobqueue = get_queued_jobs();
		}
		
		// 
		// Get state of job:
		// 
		$state = collect_job_state($jobdir, $jobqueue);
		if($options->debug) {
		    printf("debug: job directory %s is in %s state\n", $file, $state);
		}
		if($state == "pending" || $state == "running") {
		    if($options->debug) {
			printf("debug: directory %s contains a pending or running job (skipped)", $file);
		    }
		    continue;
		}
		
		// 
		// Get queued, started and finished timestamps:
		// 
		$queued   = collect_file_content(sprintf("%s/queued", $jobdir), 0);
		$started  = collect_file_content(sprintf("%s/started", $jobdir), 0);
		$finished = collect_file_content(sprintf("%s/finished", $jobdir), 0);
		
		// 
		// Get datetime parts of queued time:
		// 
		$date = getdate($queued);
		$year  = $date['year'];
		$month = sprintf("%02d", $date['mon']);
		$day   = sprintf("%02d", $date['mday']);
		
		// 
		// Save submit, state and process accounting statistics into array:
		// 
		collect_submit_count($hostid, $data, $year, $month, $day);
		collect_submit_count("all", $data, $year, $month, $day);
		collect_state_count($hostid, $data, $state);
		collect_state_count("all", $data, $state);
		// 
		// Only count finished jobs with result.
		// 
		if($finished > 0 && ($state == "success" || $state == "warning")) {
		    collect_process_accounting($hostid, $data, $queued, $started, $finished, $year, $month, $day);
		    collect_process_accounting("all", $data, $queued, $started, $finished, $year, $month, $day);
		}
		
		// 
		// Flag directory as collected.
		// 
		file_put_contents($collected, time());
	    }
	}
	closedir($handle);
    }
    else {
	die(sprintf("%s: failed reading directory %s\n", $options->prog, $hiddir));
    }
}

function collect_flush_data($topdir, $data, $options)
{
    $summary = sprintf("%s/summary.dat", $topdir);    
    if(file_exists($summary)) {
	unlink($summary);
    }
    foreach($data as $sect => $arr) {
	if(is_numeric($sect)) {
	    $subdir = sprintf("%s/%s", $topdir, $sect);
	    if(!file_exists($subdir)) {
		if(!mkdir($subdir)) {
		    die(sprintf("%s: failed create directory %s\n", $options->prog, $subdir));
		}
	    }
	    collect_flush_data($subdir, $arr, $options);
	}
	else {
	    $handle = fopen($summary, "a");
	    if(!$handle) {
		die(sprintf("%s: failed append data to file %s\n", $options->prog, $summary));
	    }
	    fprintf($handle, "[%s]\n", $sect);
	    foreach($arr as $key => $val) {
		fprintf($handle, "%s = %s\n", $key, $val);
	    }
	    fprintf($handle, "\n");
	    fclose($handle);
	}
    }
}

// 
// Write collected statistics to the filesystem.
// 
function collect_flush_stats($statdir, $statdata, $options)
{
    foreach($statdata as $hostid => $arr) {	    
	$subdir = sprintf("%s/%s", $statdir, $hostid);
	if(!file_exists($subdir)) {
	    if($options->verbose) {
		printf("updating statistics for %s\n", $hostid);
	    }
	    if(!mkdir($subdir)) {
		die(sprintf("%s: failed create directory %s\n", $options->prog, $subdir));
	    }
	}
	collect_flush_data($subdir, $arr, $options);
    }
}

// 
// Collect statistics from job directories.
// 
function collect_statistics($jobsdir, $statdir, $options)
{    
    $statfile = sprintf("%s/cache.ser", $statdir);    // collected data cached from previous runs

    $queued = null;
    if($options->qmode == LIST_QUEUE_ONCE) {
	$queued = get_queued_jobs();
    }
    
    if(!is_dir($jobsdir) && !is_link($jobsdir)) {
	die(sprintf("%s: path %s is not a directory\n", $options->prog, $jobsdir));
    }
    
    // 
    // Create statistics directories if missing:
    // 
    foreach(array( "$statdir", "$statdir/date", "$statdir/hostid", "$statdir/misc") as $subdir)
      if(!file_exists($subdir)) {
	  if($options->debug) {
	      printf("debug: creating directory '%s'\n", $subdir);
	  }
	  if(!mkdir($subdir)) {
	      die(sprintf("%s: failed create directory '%s'\n", $options->prog, $subdir));
	  }
    }
    
    // 
    // Load statistics data from previous runs:
    // 
    $statdata = array();
    if(!file_exists($statfile)) {
	$options->force = true;
    }
    if(!$options->force) {
	if(file_exists($statfile)) {
	    if($options->debug) {
		printf("debug: reading collected data from serialized cache (%s)\n", $statfile);
	    }
	    $statdata = unserialize(file_get_contents($statfile));
	}
    }
    
    // 
    // Collect statistics:
    // 
    $handle = @opendir($jobsdir);
    if($handle) {
	if($options->debug) {
	    printf("debug: processing host ID directories under %s\n", $jobsdir);
	}
	while(($file = readdir($handle)) !== false) {
	    if($file != "." && $file != "..") {		
		if($options->debug) {
		    printf("debug: processing host ID directory %s\n", $file);
		}
		collect_hostid_data($file, $statdir, $options, $statdata, $queued);
	    }
	}
	closedir($handle);
    }
    else {
	die(sprintf("%s: failed reading directory %s\n", $options->prog, $jobsdir));
    }
    
    // 
    // Save statistics for next run:
    // 
    if($options->debug) {
	printf("debug: writing collected data to serialized cache (%s)\n", $statfile);
	print_r($statdata);
    }
    file_put_contents($statfile, serialize($statdata));

    // 
    // Flush collected statistics to filesystem:
    // 
    collect_flush_stats($statdir, $statdata, $options);    
}

// 
// The main function.
//
function main(&$argv, $argc)
{
    $prog = basename(array_shift($argv));
    $vers = trim(file_get_contents("../VERSION"));
    
    // 
    // Setup defaults in options array:
    // 
    $options = array( "force"   => false,
		      "qmode"   => LIST_QUEUE_ONCE,
		      "debug"   => false, 
		      "verbose" => 0,
		      "prog"    => $prog, 
		      "version" => $vers );
    
    // 
    // Fill $options with command line options.
    // 
    $options = (object)$options;
    parse_options($argv, $argc, $options);

    // 
    // Dump options:
    //
    if($options->debug) {
	var_dump($options);
    }

    // 
    // Begin collect statistics.
    // 
    collect_statistics(sprintf("%s/jobs", CACHE_DIRECTORY), 
		       sprintf("%s/stat", CACHE_DIRECTORY), 
		       $options); 
}

// 
// Start normal script execution.
// 
main($_SERVER['argv'], $_SERVER['argc']);

?>
