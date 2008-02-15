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
// Show details about a job.
// 

//
// Get configuration.
// 
include "../conf/config.inc";
include "../include/common.inc";
include "../include/ui.inc";

// 
// Get request parameters.
// 
$jobid  = $_REQUEST['jobid'];    // Job ID.
$jobdir = $_REQUEST['result'];   // Job directory.
if(isset($_REQUEST['data'])) {
    $indata = $_REQUEST['data']; // Show indata.
}

// 
// Get hostid from cookie.
// 
$hostid = $_COOKIE['hostid'];

// 
// Sanity check:
// 
if(!isset($hostid)) {
    die("Failed get host ID. Do you have cookies enabled?");
}
if(!isset($jobid) || !isset($jobdir)) {
    die("One or more required request parameters is missing or unset");
}

// 
// Build absolute path to job directory:
// 
$jobdir = sprintf("%s/jobs/%s/%s", CACHE_DIRECTORY, $hostid, $jobdir);

// 
// If job directory is missing, the show an error message.
// 
if(!file_exists($jobdir)) {
    die("The job directory is missing");
}

function print_title()
{
    if(isset($GLOBALS['indata'])) {
	printf("Data for Job ID %d", $GLOBALS['jobid']);
    }
    else {
	printf("Details for Job ID %d", $GLOBALS['jobid']);
    }
}

function print_body()
{
    if(isset($GLOBALS['indata'])) {
	printf("<h3>Data for Job ID %d</h3><hr>\n", $GLOBALS['jobid']);
	print "<p><pre>\n";
	readfile(sprintf("%s/indata", $GLOBALS['jobdir']));
	print "</pre></p>\n";
    }
    else {
	printf("<h3>Details for Job ID %d</h3><hr>\n", $GLOBALS['jobid']);
	$cwd = getcwd();
	chdir($GLOBALS['jobdir']);
	// 
	// Display job details.
	// 
	print "<p><table>";
	if(file_exists("started")) {
	    printf("<tr><td><b>Started:</b></td><td>%s</td></tr>\n", format_timestamp(trim(file_get_contents("started"))));
	}
	if(file_exists("finished")) {
	    printf("<tr><td><b>Finished:</b></td><td>%s</td></tr>\n", format_timestamp(trim(file_get_contents("finished"))));
	}
	print "</table></p>\n";
	if(file_exists("stdout")) {
	    printf("<p><b>Output:</b><br><pre>%s</pre></p>\n", file_get_contents("stdout"));
	}
	if(file_exists("stderr")) {
	    printf("<p><b>Error:</b><br><pre>%s</pre></p>\n", file_get_contents("stderr"));
	}
	
	printf("<p><a href=\"details.php?jobid=%d&result=%s&data=1\" target=\"_blank\">View indata...</a></p>", 
	       $_REQUEST['jobid'], $_REQUEST['result']);
	chdir($cwd);
    }
}

function print_html($what)
{
    switch($what) {
     case "body":
	print_body();
	break;
     case "title":
	print_title();
	break;
     default:
	print_common_html($what);    // Use default callback.
	break;
    }
}

include "../template/popup.ui";

?>
