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
// Classify uploaded molecules and save them to the molecule database.
// 
// This script depends on data from other scripts:
// * hooks.inc: 
//   The post_enqueue_hook() function adds job directories to classify modules 
//   from in the spool file cache/incoming/hostidXX
// * chemgps-sqp-prepare.pl:
//   This script adds the classification of dragonX output to the file
//   cache/jobs/.../dragonx.dbmap
// 
// Here we do reverse mapping of molecule names in indata against dragonX 
// output (these are *not* in order). The molecules are then appended to the
// accept/reject arrays and, if not in database yet, saved to the database.
// 

include "../conf/config.inc";
include "../include/common.inc";
include "../include/getopt.inc";

//
// Show basic usage.
//
function classify_usage($prog, $sect)
{    
    print "$prog - classify uploaded molecules and save them to the database\n";
    print "\n";      
    print "Usage: $prog options...\n";
    print "Options:\n";
    print "\n";    
    print "  Standard options:\n";
    print "    -f,--force:       Force update of database.\n";
    print "    -j,--jobdir=path: Process this jobdirectory.\n";
    print "    -s,--spool=file:  Process this spool file.\n";
    print "    -k,--keep:        Keep processed spool files (useful for debug).\n";
    print "    -r,--rejected:    Also classify rejected molecules.\n";
    print "    -d,--debug:       Enable debug.\n";
    print "    -v,--verbose:     Be more verbose.\n";
    print "    -h,--help:        This help.\n";
    print "    -V,--version:     Show version info.\n";
    print "\n";
    print "Notes:\n";
    print "  The default action is to process all spool files. Use -j or -s to\n";
    print "  restrict processing to a subset.\n";
}

//
// Show verison info.
//
function classify_version($prog, $vers)
{
    print "$prog - classify uploaded molecules ($vers)\n";
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
	 case "-j":
	 case "--jobdir":
	    check_arg($key, $val, true, $options->prog);
	    $options->jobdir = $val;
	    break;
	 case "-s":
	 case "--spool":
	    check_arg($key, $val, true, $options->prog);
	    $options->spool = $val;
	    break;
	 case "-k":
	 case "--keep":
	    check_arg($key, $val, false, $options->prog);
	    $options->keep = true;
	    break;
	 case "-r":
	 case "--rejected":
	    check_arg($key, $val, false, $options->prog);
	    $options->rejected = true;
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
	    classify_usage($options->prog, $val);
	    exit(0);
	 case "-V":
	 case "--version":         // Show version info.
	    check_arg($key, $val, false, $options->prog);
	    classify_version($options->prog, $options->version);
	    exit(0);	      
	 default:
	    die(sprintf("%s: unknown option '%s', see --help\n", $options->prog, $key));
	}
    }	      
}

// 
// Process spool files. We got one per hostid under incoming.
// 
function process_spool_files($incoming, $options)
{   
    if($options->debug) {
	printf("debug: processing incoming directory %s\n", $incoming);
    }
    
    if(is_dir($incoming)) {
	if($handle = opendir($incoming)) {
	    while(($hostid = readdir($handle)) !== false) {
		if($hostid != "." && $hostid != "..") {
		    process_spool_file(sprintf("%s/%s", $incoming, $hostid), $hostid, $options);
		}
	    }
	    closedir($handle);
	}
    }
    else {
	die(sprintf("%s: incoming directory do not exists (%s)\n", $options->prog, $incoming));
    }
}

// 
// Process the spool file for hostid.
// 
function process_spool_file($spool, $hostid, $options)
{
    if($options->debug) {
	printf("debug: processing spool file %s\n", basename($spool));
    }
    
    $handle = fopen($spool, "r");
    if($handle) {
	while($str = fgets($handle)) {
	    list($jobdir, $ipaddr) = explode("\t", $str);
	    process_jobdir_metadata($jobdir, trim($ipaddr), $hostid, $options);
	}
	fclose($handle);
	if(!$options->keep) {
	    if($options->verbose) {
		printf("deleting processed spool file %s\n", $spool);
	    }
	    unlink($spool);
	}
    }
}

// 
// Process the job directory.
// 
function process_jobdir_metadata($jobdir, $ipaddr, $hostid, $options)
{
    if($options->debug) {
	printf("debug: processing job directory %s\n", basename($jobdir));
    }

    // 
    // Destination directories of classified molecules.
    // 
    $fileacc = sprintf("%s/db/accepted", CACHE_DIRECTORY);
    $filerej = sprintf("%s/db/rejected", CACHE_DIRECTORY);
    
    if(!file_exists($fileacc)) {
	die(sprintf("%s: directory for accepted molecules do not exist\n", $options->prog));
    }
    if(!file_exists($filerej)) {
	die(sprintf("%s: directory for rejected molecules do not exist\n", $options->prog));
    }
    
    // 
    // Read classification map of dragonX output. The *.dbmap file may not exists if
    // its job directory has been deleted by the user.
    // 
    $dbfile = sprintf("%s/dragonx.dbmap", $jobdir);
    if(file_exists($dbfile)) {
	$dbmap = file_get_contents($dbfile);
	if(strlen($dbmap) == 0) {
	    die(sprintf("%s: failed read dbmap\n", $options->prog));
	}
    } else {
	$dbdir = dirname($dbfile);
	if(file_exists($dbdir)) {
	    die(sprintf("%s: no %s in job directory %s\n", basename($dbfile), $dbdir));
	}
	if($options->debug) {
	    printf("debug: dbmap %s don't exists (job directory deleted bu user)\n", $dbfile);
	}
	return;
    }
    
    // 
    // The submitted indata and result from dragonX:
    // 
    $indata = sprintf("%s/indata", $jobdir);
    $dragon = sprintf("%s/chemgps.output", $jobdir);
    if(!file_exists($indata)) {
	die(sprintf("%s: indata file %s don't exist\n", $options->prog, $indata));
    }
    if(!file_exists($dragon)) {
	die(sprintf("%s: dragonX result file %s don't exist\n", $options->prog, $dragon));
    }
    
    // 
    // Contruct the index map between dragonX output and uploaded data:
    // 
    $index = 0;
    $idmap = array();
    $handle = fopen($dragon, "r");
    if($handle) {
	while($str = fgets($handle)) {
	    $match = array();
	    if(preg_match('/^\[(\d+)\]/', $str, $match) == 1) {
		$idmap[--$match[1]] = $index++;
		if($options->debug && $options->verbose) {
		    printf("debug: idmap: index=%d, mapping %d -> %d [match -> index]\n", 
			   $idmap[$match[1]], $match[1], ($index - 1));
		}
	    }
	    else if(preg_match('/^MOLID/', $str)) {
		continue;
	    }
	    else {
		if($options->force) {
		    printf("%s: warning: unexpected indata (%s...) found, but ignored due to force option\n",
			   $options->prog, substr($str, 0, 14));
		}
		else {
		    die(sprintf("%s: expected string starting with [nnn] or MOLID (reading file %s)\n",
				$options->prog, basename($dragon)));
		}
		$index++;       // We need to keep index in sync
	    }
	}
	fclose($handle);
    }
    else {
	die(sprintf("%s: failed open dragonX output %s in job directory %s\n", 
		    $options->prog, basename($dragon), basename($jobdir)));
    }
    
    // 
    // Begin process indata file:
    // 
    $handle = fopen($indata, "r");
    $index = 0;
    if($handle) {
	while($str = fgets($handle)) {
	    $match = array();
	    if(preg_match('/^(.*?)\s+\[\d+\]\s+(.*)/', $str, $match)) {
		switch($dbmap[$idmap[$index]]) {
		 case 'a':
		    update_database($match[1], $match[2], $fileacc, "accepted", $ipaddr, $hostid, $options);
		    break;
		 case 'r':
		    if($options->rejected) {
			update_database($match[1], $match[2], $filerej, "rejected", $ipaddr, $hostid, $options);
		    }
		    break;
		 default:
		    die(sprintf("%s: unknown classification %c\n", 
				$options->prog, $dbmap[$idmap[$index]]));
		}
	    }
	    ++$index;
	}
    }
}

// 
// Update the molecule database.
// 
// $molecule:  the molecule (smiles format).
// $name:      optional name.
// $dbdir:     classified molecules directory.
// $type:      the class name (accepted or rejected).
// $ipaddr:    submitter address.
// $hostid:    submitted ID.
// $options:   command line options.
// 
function update_database($molecule, $name, $dbdir, $type, $ipaddr, $hostid, $options)
{
    if($options->debug) {
	printf("debug: consider updating %s database with molecule %s\n", $type, $molecule);
    }

    $md5sum = md5($molecule);
    $dbfile = sprintf("%s/%s", $dbdir, $md5sum);
    if(!file_exists($dbfile)) {
	// 
	// This is an new smiles string, save it to its molecule
	// file and in the index.
	// 
	if($options->verbose) {
	    printf("saving new molecule (%s) to database (%s)\n", isset($name) ? $name : $molecule, $type);
	}
	$name = isset($name) ? $name : "---";
	$date = date('Y-m-d H:i');
	$idfile = sprintf(sprintf("%s/index", $dbdir));
	file_put_contents($dbfile, $molecule);
	$idfmt = "%-20s\t%-32s\t%-20s\t%-40s\t%s\n";
	if(!file_exists($idfile)) {
	    file_put_contents($idfile, sprintf($idfmt, "Date/Time:", "Host ID:", "IP-address:", "Molecule:", "Name:"));
	    file_put_contents($idfile, sprintf($idfmt, "----------", "--------", "-----------", "---------", "-----"), FILE_APPEND);
	}
	file_put_contents($idfile, sprintf($idfmt, $date, $hostid, $ipaddr, $molecule, $name), FILE_APPEND);
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
    $options = array( "force"    => false,
		      "debug"    => false, 
		      "jobdir"   => null,
		      "spool"    => null,
		      "keep"     => false,
		      "rejected" => false,
		      "verbose"  => 0,
		      "prog"     => $prog, 
		      "version"  => $vers );
    
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
    // Begin process:
    // 
    if(isset($options->spool)) {
	$hostid = basename($options->spool);
	if(file_exists($options->spool)) {
	    process_spool_file($options->spool, $hostid, $options);
	}
	else if($options->debug) {
	    printf("debug: spool file %s don't exist\n", $options->spool);
	}
    }
    else if(isset($options->jobdir)) {
	if(file_exists($options->jobdir)) {
	    $hostid = basename($options->jobdir);
	    $spool  = sprintf("%s/db/incoming/%s", CACHE_DIRECTORY, $hostid);
	    if(file_exists($spool)) {
		process_spool_file($spool, $hostid, $options);
	    }
	    else if($options->debug) {
		printf("debug: job directory %s has no spool file\n", $options->jobdir);
	    }
	}
	else {
	    die(sprintf("%s: job directory %s don't exist\n", $options->prog, $options->jobdir));
	}
    }
    else {
	process_spool_files(sprintf("%s/db/incoming", CACHE_DIRECTORY), $options);
    }
}

// 
// Start normal script execution.
// 
main($_SERVER['argv'], $_SERVER['argc']);

?>
