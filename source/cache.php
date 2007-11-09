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
// Cache management. This script should be runned either from the 
// command line or as a cron job.
// 

//
// The script should only be run in CLI mode.
//
if(isset($_SERVER['HTTP_USER_AGENT'])) {
    die("This script should be runned in CLI mode.\n");
}

include "../conf/config.inc";
include "../include/getopt.inc";

//
// Show basic usage.
//
function cache_usage($prog)
{
    print <<< END
$prog - send notifications to event listener
      
Usage: $prog options...
Options:
    
  Actions:
    -c,--cleanup:         Delete job directories.
    -l,--list:            List job directories.
    -f,--find:            Used with -x or -i for lookups.
      
  Filters:
    -x,--hostid=str:      Filter on hostid.
    -i,--ipaddr=str:      Filter on ip-address or hostname.
    -a,--age=timespec:    Filter on timespec. The timespec string is a number and
                          a suffix character. The following suffix characters can
                          be used: 
                          s = second, m = minute, h = hour, 
                          D = day, W = week, M = month, Y = year

  Miscellanous options:
    --dry-run:            Just print what should have be done, but do not perform 
                          any modifications. Useful together with --cleanup
    -m,--machine:         Generate output for parsing by other program or scripts.
      
  Standard options:  
    -d,--debug:           Enable debug.
    -v,--verbose:         Be more verbose.
    -h,--help:            This help.
    -V,--version:         Show version info.
      
Example:
    * Delete all job directories older than seven days:
    bash$> $prog -c -a 7d
      
    * Get hostid for host host.domain.com:
    bash$> $prog -f -i host.domain.com

    * Reverse lookup from hostid 837ec5754f503cfaaee0929fd48974e7:
    bash$> $prog -f -x 837ec5754f503cfaaee0929fd48974e7
      
    * List all jobs created from host host.domain.com:
    bash$> $prog -l -i host.domain.com
    
    * Delete all jobs older than three month created from ip-address 192.168.10.56
    bash$> $prog -c -a 12W -i 192.168.10.56
      
Warning:
    If no filter (-a, -i or -x) is in effect, then the action (-c or -l) is
    applied to all. Use --dry-run to test.

END;
}

//
// Show verison info.
//
function cache_version($prog, $vers)
{
    print "Cache management tool $prog ($vers)\n";
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
	 case "-c":
	 case "--cleanup":         // Delete job directories.
	    check_arg($key, $val, false, $options->prog);
	    $options['cleanup'] = true;
	    break;	    
	 case "-l":
	 case "--list":            // List job directories.
	    check_arg($key, $val, false, $options->prog);
	    $options['list'] = true;
	    break;
	 case "-f":
	 case "--find":            // Perform hostid or ip-address/hostname lookup.
	    check_arg($key, $val, false, $options->prog);
	    $options['find'] = true;
	    break;
	 case "-x":
	 case "--hostid":          // Filter on hostid.
	    check_arg($key, $val, true, $options->prog);
	    $options['hostid'] = $val;
	    break;
	 case "-i":
	 case "--ipaddr":          // Filter on ip-address or hostname.
	    check_arg($key, $val, true, $options->prog);
	    $ipaddr = val;
	    if(!is_numeric($val[0])) {
		$ipaddr = gethostbyname($val);
		if($ipaddr == $val) {
		    die(sprintf("%s: failed resolve hostname %s\n", $options->prog, $val));
		}
	    }
	    $options['ipaddr'] = $val;	
	    break;
	 case "-a":
	 case "--age":             // Filter on timespec. The timespec string is a number and
	    check_arg($key, $val, true, $options->prog);
	    $match = array();
	    if(!preg_match("/^(\d+)([smhDWMY])$/", $val, $match)) {
		die(sprintf("%s: wrong format for argument to option '%s', see --help\n", $options->prog, $key));
	    }
	    // 
	    // Calculate the timestamp used when comparing modification times.
	    // 
	    $map = array( "s" => "second", "m" => "minute", "h" => "hour",
			  "D" => "day", "W" => "week", "M" => "month", "Y" => "year" );
	    $timespec = sprintf("-%s %s", $match[1], $map[$match[2]]);
	    $options['age'] = strtotime($timespec);
	    break;
	 case "--dry-run":         // Just print what should have be done.
	    check_arg($key, $val, false, $options->prog);
	    $options['dry_run'] = true;
	    break;
	 case "-m":
	 case "--machine":         // Generate output for parsing by other program or scripts.
	    check_arg($key, $val, false, $options->prog);
	    $options['machine'] = true;
	    break;
	 case "-d":
	 case "--debug":           // Enable debug.
	    check_arg($key, $val, false, $options->prog);
	    $options['debug'] = true;
	    break;
	 case "-v":
	 case "--verbose":         // Be more verbose.
	    check_arg($key, $val, false, $options->prog);
	    $options['verbose']++;
	    break;
	 case "-h":
	 case "--help":            // Show help.
	    check_arg($key, $val, false, $options->prog);
	    cache_usage($options['prog']);
	    exit(0);
	 case "-V":
	 case "--version":         // Show version info.
	    check_arg($key, $val, false, $options->prog);
	    cache_version($options['prog'], $options['version']);
	    exit(0);	      
	 default:
	    die(sprintf("%s: unknown option '%s', see --help\n", $options->prog, $key));
	}
    }	      
}

// 
// Lookup hostid from ip-address or hostname.
// 
function cache_get_hostid($ipaddr, $options)
{
    $mapfile = sprintf("%s/map/inaddr/%s", CACHE_DIRECTORY, $ipaddr);
    if($options->debug) {
	printf("debug: looking for hostid in file %s\n", $mapfile);
    }
    if(file_exists($mapfile)) {
	return trim(file_get_contents($mapfile));
    }
    else {
	die(sprintf("%s: failed find hostid for '%s' (maybe its using ipv6?)\n", $options->prog, $ipaddr));
    }
}

// 
// Lookup ip-address from hostid.
// 
function cache_get_ipaddr($hostid, $options)
{
    $mapfile = sprintf("%s/map/hostid/%s", CACHE_DIRECTORY, $hostid);
    if($options->debug) {
	printf("debug: looking for ip-address in file %s\n", $mapfile);
    }
    if(file_exists($mapfile)) {
	return trim(file_get_contents($mapfile));
    }
    else {
	die(sprintf("%s: failed find ip-address for '%s'\n", $options->prog, $hostid));
    }
}

// 
// Find jobs matching filter options.
// 
function cache_find_jobs($hostid, $options)
{
    $dirname = sprintf("%s/jobs/%s", CACHE_DIRECTORY, $hostid);

    if($options->debug) {
	printf("debug: processing job dirctories of hostid %s\n", $hostid);
    }
    
    $handle = opendir($dirname);
    if($handle) {
	$result = array();
	while(false !== ($dir = readdir($handle))) {
	    if($dir != "." && $dir != "..") {
		if($options->age) {
		    // 
		    // Only append directories older than age filter.
		    // 
		    if(intval($dir) > $options->age) {
			if($options->debug) {
			    printf("debug: directory %s is newer than %d (skipped)\n", 
				   $dir, $options->age);
			}
			continue;
		    }
		}
		if($options->debug) {
		    printf("debug: appending %s to list of job directories.\n", $dir);
		}
		array_push($result, $dir);
	    }
	}
	closedir($handle);
	return $result;
    }
    else {
	die(sprintf("%s: failed open directory %s\n", $options->prog, $dirname));
    }
}

// 
// Find all job directories matching filter preferences.
// 
function cache_find_job_dirs($options)
{
    if(isset($options->ipaddr)) {
	$options->hostid = cache_get_hostid($options->ipaddr, $options);
	if($options->debug) {
	    printf("debug: resolved ip-address %s to hostid %s\n", 
		   $options->ipaddr, 
		   $options->hostid);
	}
    }
    
    // 
    // Begin find directories in job cache.
    // 
    if(isset($options->hostid)) {
	// 
	// Only consider this single hostid.
	//
	$result = array();
	$result[$options->hostid] = cache_find_jobs($options->hostid, $options);
	return $result;
    }
    else {
	// 
	// Process multiple hostid's.
	// 
	$jobsdir = sprintf("%s/jobs", CACHE_DIRECTORY);
	$handle = opendir($jobsdir);
	if($handle) {
	    $result = array();
	    while(false !== ($dir = readdir($handle))) {
		if($dir != "." && $dir != "..") {
		    $result[$dir] = cache_find_jobs($dir, $options);
		}
	    }
	    closedir($handle);
	    return $result;
	}
	else {
	    die(sprintf("%s: failed open directory %s\n", $options->prog, $jobsdir));
	}
    }
}

// 
// Return size of job directory including contained files.
//
function cache_get_job_size($path, &$data)
{
    if(file_exists($path)) {
	$handle = opendir($path);
	if($handle) {
	    while(false !== ($file = readdir($handle))) {
		if($file != "." && $file != "..") {
		    if(is_dir($file)) {
			$data['size'] += cache_get_job_size(sprintf("%s/%s", $path, $file));
			return;
		    }
		    else {
			$data['files']++;
			$data['size'] += filesize(sprintf("%s/%s", $path, $file));
		    }
		}
	    }
	    closedir($handle);
	}	
    }
    
    $data['size'] += filesize($path);
}

// 
// Recursive delete a job directory.
// 
function cache_delete_directory($path, $options)
{  
    if($options->debug) {
	printf(sprintf("debug: deleting in directory %s\n", basename($path)));
    }
    
    $handle = opendir($path);
    if($handle) {
	while(false !== ($file = readdir($handle))) {
	    if($file != "." && $file != "..") {
		$curr = sprintf("%s/%s", $path, $file);
		if(is_dir($curr)) {
		    $result = cache_delete_directory($curr, $options);
		    if(!rmdir($curr)) {
			printf("%s: failed remove directory %s\n", $options->prog, $file);
			return false;
		    }
		    return $result;
		}
		else {
		    if($options->dry_run) {
			printf(sprintf("debug: whould have deleted file %s (dry-run mode)\n", $file));
		    }
		    else {
			if($options->debug) {
			    printf("debug: deleting file %s\n", $file);
			}
			if(!unlink($curr)) {
			    printf(sprintf("%s: failed unlink file %s\n", $options->prog, $file));
			    return false;
			}
		    }
		}
	    }
	}
	closedir($handle);
    }
    
    if($options->debug) {
	printf("debug: deleting directory %s\n", basename($path));
    }
    if(!rmdir($path)) {
	printf(sprintf("%s: failed remove directory %s\n", $options->prog, basename($path)));
	return false;
    }
    if($options->verbose) {
	printf("deleted directory %s\n", $path);
    }
    
    return true;
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
    $options = array( "cleanup" => false,
		      "list" => false,
		      "find" => false,
		      "hostid" => null,
		      "ipaddr" => null,
		      "age" => 0,
		      "now" => time(),
		      "dry_run" => false,
		      "machine" => false,
		      "debug" => false, 
		      "verbose" => 0,
		      "prog"  => $prog, 
		      "version" => $vers );
    
    // 
    // Fill $options with command line options.
    // 
    parse_options($argv, $argc, $options);
    $options = (object)$options;
    
    // 
    // Dump options:
    //
    if($options->debug) {
	var_dump($options);
    }

    // 
    // Perform sanity check on options.
    // 
    if(isset($options->hostid) && isset($options->ipaddr)) {
	die(sprintf("%s: option '--hostid' should not be used together with option '--ipaddr'\n", $options->prog));
    }

    // 
    // Process options.
    // 
    if(isset($options->find)) {
	if(isset($options->ipaddr)) {
	    $options->hostid = cache_get_hostid($options->ipaddr, $options);
	    printf("%s: %s\n", $options->ipaddr, $options->hostid);
	}
	if(isset($options->hostid)) {
	    $options->ipaddr = cache_get_ipaddr($options->hostid, $options);
	    printf("%s: %s\n", $options->hostid, $options->ipaddr);
	}	    
    }

    if($options->list) {
	// 
	// Get all job directories matching filter preferences.
	// 
	$dirs = cache_find_job_dirs($options);
	foreach($dirs as $hostid => $jobdirs) { 
	    if($options->machine) {
		foreach($jobdirs as $jobdir) {
		    printf("%s/jobs/%s/%s\n", CACHE_DIRECTORY, $hostid, $jobdir);
		}
	    }
	    else {
		printf("%s (hostid)\n", $hostid);
		foreach($jobdirs as $jobdir) {
		    if($options->verbose) {
			$path = sprintf("%s/jobs/%s/%s", CACHE_DIRECTORY, $hostid, $jobdir);
			$data = array();
			cache_get_job_size($path, $data);
			printf("  %s (size = %d, files = %d, created = %s, modified = %s) (jobdir)\n", $jobdir,
			       $data['size'], $data['files'], 
			       strftime(TIMESTAMP_FORMAT, $jobdir),
			       strftime(TIMESTAMP_FORMAT, filemtime($path)));
		    }
		    else {
			printf("  %s (jobdir)\n", $jobdir);
		    }
		}
	    }
	}
    }

    if($options->cleanup) {
	// 
	// Get all job directories matching filter preferences.
	// 
	$dirs = cache_find_job_dirs($options);	
	foreach($dirs as $hostid => $jobdirs) { 
	    foreach($jobdirs as $jobdir) {
		$path = sprintf("%s/jobs/%s/%s", CACHE_DIRECTORY, $hostid, $jobdir);
		if($options->debug || $options->dry_run) {
		    printf("debug: about to delete directory %s (hostid = %s)\n", $jobdir, $hostid);
		}
		if($options->debug || $options->verbose) {
		    printf("%sdeleting directory %s (hostid = %s)\n", ($options->debug ? "debug: " : ""), $jobdir, $hostid);
		}
		cache_delete_directory($path, $options);
	    }
	}
    }
}

// 
// Start normal script execution.
// 
main($_SERVER['argv'], $_SERVER['argc']);

?>
