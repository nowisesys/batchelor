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
    -f,--find=ipaddr:     Lookup hostid from ip-address or hostname.
      
  Filters:
    -x,--hostid=str:      Filter on hostid.
    -i,--ipaddr=str:      Filter on ip-address or hostname.
    -a,--age=timespec:    Filter on timespec. The timespec string is a number and
                          a suffix character. The following suffix characters can
                          be used: 
                          s = second, m = minute, h = hour, 
                          D = day, W = week, M = month, Y = year
                           
  Standard options:  
    -d,--debug:           Enable debug.
    --dry-run:            Just print what should have be done, but do not perform 
                          any modifications. Useful together with --cleanup
    -v,--verbose:         Be more verbose.
    -h,--help:            This help.
    -V,--version:         Show version info.
      
Example:
    * Delete all job directories older than seven days:
    bash$> $prog -c -a 7d
      
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
function check_arg($key, $val, $required)
{
    if($required) {
	if(!isset($val)) {
	    die(sprintf("option '%s' requires an argument\n", $key));
	}
    }
    else {
	if(isset($val)) {
	    die(sprintf("option '%s' do not take an argument\n", $key));
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
	    check_arg($key, $val, false);
	    $options['cleanup'] = true;
	    break;	    
	 case "-l":
	 case "--list":            // List job directories.
	    check_arg($key, $val, false);
	    $options['list'] = true;
	    break;
	 case "-f":
	 case "--find=ipaddr":     // Lookup hostid from ip-address or hostname.
	    check_arg($key, $val, true);
	    $ipaddr = val;
	    if(!is_numeric($val[0])) {
		$ipaddr = gethostbyname($val);
		if($ipaddr == $val) {
		    die(sprintf("failed resolve hostname %s\n", $val));
		}
	    }
	    $options['find'] = $val;
	    break;
	 case "-x":
	 case "--hostid":          // Filter on hostid.
	    check_arg($key, $val, true);
	    $options['hostid'] = $val;
	    break;
	 case "-i":
	 case "--ipaddr":          // Filter on ip-address or hostname.
	    check_arg($key, $val, true);
	    $ipaddr = val;
	    if(!is_numeric($val[0])) {
		$ipaddr = gethostbyname($val);
		if($ipaddr == $val) {
		    die(sprintf("failed resolve hostname %s\n", $val));
		}
	    }
	    $options['ipaddr'] = $val;	
	    break;
	 case "-a":
	 case "--age":             // Filter on timespec. The timespec string is a number and
	    check_arg($key, $val, true);
	    $match = array();
	    if(!preg_match("/^(\d+)([smhDWMY])$/", $val, $match)) {
		die(sprintf("wrong format for argument to option '%s', see --help\n", $key));
	    }
	    // 
	    // Calculate the timestamp used when comparing modification times.
	    // 
	    $map = array( "s" => "second", "m" => "minute", "h" => "hour",
			  "D" => "day", "W" => "week", "M" => "month", "Y" => "year" );
	    $timespec = sprintf("-%s %s", $match[1], $map[$match[2]]);
	    $options['age'] = strtotime($timespec);
	    break;
	 case "-d":
	 case "--debug":           // Enable debug.
	    check_arg($key, $val, false);
	    $options['debug'] = true;
	    break;
	 case "--dry-run":         // Just print what should have be done.
	    check_arg($key, $val, false);
	    $options['dry-run'] = true;
	    break;
	 case "-v":
	 case "--verbose":         // Be more verbose.
	    check_arg($key, $val, false);
	    $options['verbose']++;
	    break;
	 case "-h":
	 case "--help":            // Show help.
	    check_arg($key, $val, false);
	    cache_usage($options['prog']);
	    exit(0);
	 case "-V":
	 case "--version":         // Show version info.
	    check_arg($key, $val, false);
	    cache_version($options['prog'], $options['version']);
	    exit(0);	      
	 default:
	    die(sprintf("unknown option '%s', see --help\n", $key));
	}
    }	      
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
		      "find" => null,
		      "hostid" => null,
		      "ipaddr" => null,
		      "age" => 0,
		      "now" => time(),
		      "debug" => false, 
		      "dry-run" => false,
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
}

// 
// Start normal script execution.
// 
main($_SERVER['argv'], $_SERVER['argc']);

?>
