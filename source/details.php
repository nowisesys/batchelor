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
// The error handler.
// 
function error_handler($type)
{
    // 
    // Redirect caller back to queue.php and let it report an error.
    // 
    header("Location: queue.php?show=queue&error=details&type=$type");
}

function print_title()
{
    if(isset($GLOBALS['indata'])) {
	printf("%s - Data for Job ID %s", HTML_PAGE_TITLE, $GLOBALS['jobid']);
    }
    else {
	printf("%s - Details for Job ID %s", HTML_PAGE_TITLE, $GLOBALS['jobid']);
    }
}

function print_body()
{
    if(isset($GLOBALS['indata'])) {
	printf("<h3>Data for Job ID %s</h3><hr>\n", $GLOBALS['jobid']);
	$content = file(sprintf("%s/indata", $GLOBALS['jobdir']));
	// 
	// Single line input data must be wrapped by browser, or
	// the output may look like empty space on the page.
	// 
	if(count($content) == 1) {
	    printf("<p>%s</p>\n", $content[0]);
	} else {
	    printf("<p><pre>%s</pre></p>\n", implode("\n", $content));
	}
    }
    else {
	printf("<h3>Details for Job ID %s</h3><hr>\n", $GLOBALS['jobid']);
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
	if(file_exists("stdout") && filesize("stdout") > 0) {
	    printf("<p><b>Output:</b><br><pre>%s</pre></p>\n", file_get_contents("stdout"));
	}
	if(file_exists("stderr") && filesize("stderr") > 0) {
	    printf("<p><b>Error:</b><br><pre>%s</pre></p>\n", file_get_contents("stderr"));
	}
	
	printf("<p><a href=\"details.php?jobid=%s&result=%s&data=1\" target=\"_blank\">View indata...</a></p>", 
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

// 
// Check required parameters.
// 
if(!isset($_REQUEST['jobid']) || !isset($_REQUEST['result'])) {
    error_handler("params");
}
if(!isset($_COOKIE['hostid'])) {
    error_handler("hostid");
}

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
// Build absolute path to job directory:
// 
$jobdir = sprintf("%s/jobs/%s/%s", CACHE_DIRECTORY, $hostid, $jobdir);

// 
// If job directory is missing, the show an error message.
// 
if(!file_exists($jobdir)) {
    die("The job directory is missing");
}

load_ui_template("popup");

?>
