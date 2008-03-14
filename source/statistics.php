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
// The statistics module.
// 

// 
// Include configuration and libs.
// 
include "../conf/config.inc";
include "../include/common.inc";
include "../include/ui.inc";
include "../include/statistics.inc";

// 
// Call exit on script if request parameter name is set but
// don't match the pattern.
// 
function check_request_param($name, $pattern)
{    
    if(isset($_REQUEST[$name])) {
	if(!preg_match("/^$pattern$/", $_REQUEST[$name])) {
	    error_exit("Invalid argument for request parameter $name.");
	}
    }
}

// 
// Print an error message and exit the script. This function should only be
// called before the template has been included.
// 
function error_exit($msg)
{
    $GLOBALS['error'] = $msg;
    include "../template/standard.ui";
    exit(1);
}

// 
// Read the summary.dat file from statistics directory.
// 
function read_summary_dat($statdir)
{
    $summary = array();
    $sumpath = sprintf("%s/summary.dat", $statdir);
    
    if(!file_exists($sumpath)) {
	return null;
    }
    
    $fs = fopen($sumpath, "r");
    if(!$fs) {
	print_message_box("error", "Statistics data file not found.");
	return null;
    }
    $sect = "";
    while(($str = fgets($fs))) {
	$matches = array();
	if(preg_match('/^\[(.*?)\]/', $str, $matches)) {
	    $sect = $matches[1];
	}
	else if(preg_match('/(.*?)\s+=\s+(.*)/', $str, $matches)) {
	    $summary[$sect][$matches[1]] = $matches[2];
	}
    }
    fclose($fs);
    
    return $summary;
}

// 
// Return list of subdirectories.
// 
function get_stat_subdirs($statdir)
{    
    $subdirs = array();
    
    $handle = opendir($statdir);
    if($handle) {
	while(($file = readdir($handle)) !== false) {
	    if($file == "." || $file == "..") {
		continue;
	    }
	    if(is_dir(sprintf("%s/%s", $statdir, $file))) {
		array_push($subdirs, $file);
	    }
	}
	closedir($handle);
    }
    return $subdirs;
}

// 
// Returns date string for a sub section.
// 
function subsect_date_string($subsect, $value = null)
{
    switch($subsect) {
     case "root":
	return "the system uptime";
     case "year":
	if($value) {
	    return $value;
	}
	else {
	    return $_REQUEST['year'];
	}
     case "month":
	if($value) {
	    return date('F Y', mktime(0, 0, 0, $value, 1, $_REQUEST['year']));
	}
	else {
	    return date('F Y', mktime(0, 0, 0, $_REQUEST['month'], 1, $_REQUEST['year']));
	}
     case "day":
	if($value) {
	    return date('Y-m-d', mktime(0, 0, 0, $_REQUEST['month'], $value, $_REQUEST['year']));
	}
	else {
	    return date('Y-m-d', mktime(0, 0, 0, $_REQUEST['month'], $_REQUEST['day'], $_REQUEST['year']));
	}
    }
}
    
// 
// Print links to subdirs section.
// 
function print_links_section($data, $subdirs, $child, $parent)
{
    $delim = "";
    printf("<span id=\"secthead\">Navigator:</span>\n");
    printf("<p><ul>\n");
    if($parent) {
	$map = array( "root" => "year", "year" => "month", "month" => "day", "day" => "hour" );
	printf("<li>Up to <a href=\"statistics.php?%s\">parent</a> directory</li>\n", request_params(array(), array( $map[$parent] )));
    }
    if($child) {
	printf("<li>Statistics by %s: ", $child);
	foreach($subdirs as $subdir) {
	    printf("<a href=\"statistics.php?%s\">%s %s </a>",
		   request_params(array( "$child" => $subdir)), 
		   $delim, 
		   subsect_date_string($child, $subdir));
	    if(strlen($delim) == 0) {
		$delim = "|";
	    }
	}
	printf("</li>\n");
    }
    printf("</ul></p>\n");
}

// 
// Print submit section.
// 
function print_submit_section($data, $statdir, $subsect)
{
    printf("<span id=\"secthead\">Submitted Jobs:</span>\n");
    
    printf("<p>The total number of submitted jobs are %d for %s</p>\n", 
	   $data['submit']['count'], 
	   subsect_date_string($subsect)); 
    
    printf("<p>\n");
    if(file_exists(sprintf("%s/submit.png", $statdir))) {
	printf("<img src=\"image.php?%s\">\n", 
	       request_params(array( "image" => "submit" )));
    }
    printf("</p>\n");
}

// 
// Print process time section.
// 
function print_proctime_section($data, $statdir, $subsect)
{
    printf("<span id=\"secthead\">Process Time:</span>\n");
    
    printf("<p>On avarage each job took <b>%.01f seconds</b> to complete (from submitted until it finished execute).<br>", 
	   $data['proctime']['waiting'] + $data['proctime']['running']);
    printf("The jobs took between <b>%d</b> and <b>%d</b> seconds to complete (min/max).</p>\n",
	   $data['proctime']['minimum'], $data['proctime']['maximum']);
    
    printf("<p><table>\n");
    printf("<tr><td>Time waiting:</td><td>%.01f seconds (avarage)</td></tr>\n", $data['proctime']['waiting']);
    printf("<tr><td>Time running:</td><td>%.01f seconds (avarage)</td></tr>\n", $data['proctime']['running']);
    printf("</table></p>\n");
    
    printf("<p>\n");
    if(file_exists(sprintf("%s/proctime.png", $statdir))) {
	printf("<img src=\"image.php?%s\">\n", 
	       request_params(array( "image" => "proctime" )));
    }
    printf("</p>\n");
}

// 
// Print state section.
// 
function print_state_section($data, $statdir, $subsect)
{
    printf("<span id=\"secthead\">Job State:</span>\n");
    
    printf("<p>Totally %d jobs was <b>finished</b> (%d of them with <b>warnings</b>). ",
	   $data['state']['warning'] + $data['state']['success'], 
	   $data['state']['warning']);
    printf("%d ended with <b>errors</b> and %d has <b>crashed</b>.",
	   $data['state']['error'], $data['state']['crashed']);
    printf("</p>\n");
    
    printf("<p>\n");
    if(file_exists(sprintf("%s/state.png", $statdir))) {
	printf("<img src=\"image.php?%s\">\n", 
	       request_params(array( "image" => "state" )));
    }
    printf("</p>\n");
}

// 
// Print system load section.
// 
function print_sysload_section($data, $statdir, $images, $subsect)
{
    printf("<span id=\"secthead\">System Load:</span>\n");

    if($data['submit']['count'] != $data['proctime']['count']) {
	printf("<p>A total of <b>%d jobs</b> was submitted during this period. %d was completed and %d failed.</p>\n",
	       $data['submit']['count'], $data['proctime']['count'], $data['submit']['count'] - $data['proctime']['count']);
    }
    else {
	printf("<p>A total of <b>%d jobs</b> was submitted during this period and all jobs completed without errors.</p>\n",
	       $data['submit']['count']);
    }
    printf("<p>On avarage each job took <b>%.01f seconds</b> to complete (from submitted until it finished execute).<br>", 
	   $data['proctime']['waiting'] + $data['proctime']['running']);
    printf("The jobs took between <b>%d</b> and <b>%d</b> seconds to complete (min/max).</p>\n",
	   $data['proctime']['minimum'], $data['proctime']['maximum']);
    
    printf("<p><table>\n");
    printf("<tr><td>Time waiting:</td><td>%.01f seconds (avarage)</td></tr>\n", $data['proctime']['waiting']);
    printf("<tr><td>Time running:</td><td>%.01f seconds (avarage)</td></tr>\n", $data['proctime']['running']);
    printf("</table></p>\n");

    printf("<p>\n");
    foreach($images as $image) {
	if(file_exists(sprintf("%s/%s.png", $statdir, $image))) {
	    printf("<p><img src=\"image.php?%s\"></p>\n", 
		   request_params(array( "image" => "$image" )));
	}
    }
    printf("</p>\n");
}

// 
// The root directory of hostid (that can be all).
// 
function stat_show_root($statdir, $hostid)
{        
    $subsect = "root";
    $data = read_summary_dat($statdir);
    if($data) {
	$subdirs = get_stat_subdirs($statdir);
	if(count($subdirs)) {
	    print_links_section($data, $subdirs, "year", null);
	}
	if($_REQUEST['stat'] == "glob" || $_REQUEST['stat'] == "pers") {
	    print_submit_section($data, $statdir, $subsect);
	    print_proctime_section($data, $statdir, $subsect);
	    print_state_section($data, $statdir, $subsect);
	}
	if($_REQUEST['stat'] == "load") {
	    print_sysload_section($data, $statdir, 
				  array( "sysload_total", "sysload_weekly", "sysload_hourly" ),
				  $subsect);
	}
    }
}

// 
// Show statistics by year.
// 
function stat_show_year($statdir, $hostid)
{    
    $subsect = "year";
    $data = read_summary_dat($statdir);
    if($data) {
	$subdirs = get_stat_subdirs($statdir);
	if(count($subdirs)) {
	    print_links_section($data, $subdirs, "month", "root");
	}
	if($_REQUEST['stat'] == "glob" || $_REQUEST['stat'] == "pers") {
	    print_submit_section($data, $statdir, $subsect);
	    print_proctime_section($data, $statdir, $subsect);
	}
	if($_REQUEST['stat'] == "load") {
	    print_sysload_section($data, $statdir, array( "sysload" ), $subsect);
	}
    }
}

// 
// Show statistics by month.
// 
function stat_show_month($statdir, $hostid)
{
    $subsect = "month";
    $data = read_summary_dat($statdir);
    if($data) {
	$subdirs = get_stat_subdirs($statdir);
	if(count($subdirs)) {
	    print_links_section($data, $subdirs, "day", "year");
	}
	if($_REQUEST['stat'] == "glob" || $_REQUEST['stat'] == "pers") {
	    print_submit_section($data, $statdir, $subsect);
	    print_proctime_section($data, $statdir, $subsect);
	}
	if($_REQUEST['stat'] == "load") {
	    print_sysload_section($data, $statdir, array( "sysload" ), $subsect);
	}
    }
}

// 
// Show statistics by day.
// 
function stat_show_day($statdir, $hostid)
{
    $subsect = "day";
    $data = read_summary_dat($statdir);
    if($data) {
	$subdirs = get_stat_subdirs($statdir);
	if(count($subdirs)) {
	    print_links_section($data, $subdirs, null, "month");
	}
	if($_REQUEST['stat'] == "glob" || $_REQUEST['stat'] == "pers") {
	    print_submit_section($data, $statdir, $subsect);
	    print_proctime_section($data, $statdir, $subsect);
	}
	if($_REQUEST['stat'] == "load") {
	    print_sysload_section($data, $statdir, array( "sysload" ), $subsect);
	}
    }
}

// 
// Return GET argument string.
// 
function request_params($include = array(), $exclude = array())
{
    $getargs = "";
    $delimit = "";
    
    // 
    // Add extra parameters.
    // 
    foreach($include as $key => $val) {
	$getargs .= sprintf("%s%s=%s", $delimit, $key, $val);
	if(!strlen($delimit)) {
	    $delimit = "&";
	}
    }
    
    // 
    // Add standard parameters not in the exclude array.
    // 
    foreach(array( "stat", "year", "month", "day" ) as $name) {
	if(isset($_REQUEST[$name])) {
	    if(!in_array($name, $exclude)) {
		$getargs .= sprintf("%s%s=%s", $delimit, $name, $_REQUEST[$name]);
		if(!strlen($delimit)) {
		    $delimit = "&";
		}
	    }
	}
    }
    
    return trim($getargs);
}

// 
// Print the tab menu.
// 
function print_stat_menu()
{
    print "<div id=\"tabmenu\"><ul>\n";
    foreach(array( "glob" => "System", "pers" => "Personal", "load" => "Load" ) as $sect => $desc) {
	if($sect == $_REQUEST['stat']) {
	    printf("<li id=\"selected\"><a href=\"statistics.php?%s\">%s</a></li><!-- Fix IE\n -->\n",
		   request_params(array( "stat" => $sect ), 
				  array( "stat" )), 
		   $desc); 
	}
	else {
	    printf("<li><a href=\"statistics.php?%s\">%s</a></li><!-- Fix IE\n -->\n",
		   request_params(array( "stat" => $sect, "switch" => 1 ),
				  array( "stat" )), 
		   $desc);
	}
    }
    print "</div></u>\n";
}

// 
// Print the page body.
// 
function print_body()
{  
    printf("<h2><img src=\"icons/nuvola/statistics.png\"> %s Statistics</h2>\n", 
	   $_REQUEST['stat'] == "pers" ? "Personal" : "System");

    if(isset($GLOBALS['error'])) {
        print_message_box("error", $GLOBALS['error']);
	return;
    }
    
    // 
    // Check that statistics have been generated:
    // 
    $statdir = sprintf("%s/stat", CACHE_DIRECTORY);
    if(!file_exists($statdir)) {
        print_message_box("info", "No statistics have been generated yet.");
	return;
    }
    
    // 
    // Find the directory to show statistics from. The idea is to support:
    // 1. Verification of legal pathes.
    // 2. Correct bogus request parameters, possibly up to stat root.
    // 
    $statdir = sprintf("%s/stat/%s", CACHE_DIRECTORY, $GLOBALS['sect']);
    foreach(array( "root" => "year", "year" => "month", "month" => "day" ) as $parent => $child) {
	if(!isset($_REQUEST[$child])) {
	    $child = $parent;
	    break;
	}
	$statdir = sprintf("%s/%s", $statdir, $_REQUEST[$child]);
	if(!file_exists($statdir)) {
	    // 
	    // Suppress error message when switching from global statistics
	    // to personal, as personal is a subset of global and might not
	    // exist for this set of request parameters.
	    // 
	    if(!(isset($_REQUEST['switch']) && $GLOBALS['sect'] == "pers")) {
		print_message_box("error", "Requested statistics not found.");
	    }
	    $child = $parent;
	    $statdir = dirname($statdir);
	    break;
	}
    }
    
    // 
    // Print the tab menu:
    //
    print_stat_menu();
    print "<br>\n";
    
    switch($child) {
     case "root":
	stat_show_root($statdir, $GLOBALS['sect']);
	break;
     case "year":
	stat_show_year($statdir, $GLOBALS['sect']);
	break;
     case "month":
	stat_show_month($statdir, $GLOBALS['sect']);
	break;
     case "day":
	stat_show_day($statdir, $GLOBALS['sect']);
	break;
    }
}

function print_html($what)
{
    switch($what) {
     case "body":
	print_body();
	break;
     case "title":
	print "Batchelor - Statistics";
	break;
     default:
	print_common_html($what);
	break;
    }
}

// 
// Main begins here:
// 

// 
// Get or set the hostid cookie.
// 
update_hostid_cookie();

// 
// Validate request parameters.
// 
check_request_param("stat",  "(glob|pers|load)");
check_request_param("year",  "(19|20)\\d{2}");    // yes, we introduces a year 2100 bug!! ;-)
check_request_param("month", "(0[1-9]|1[0-2])");
check_request_param("day",   "(0[1-9]|[12][0-9]|3[0-1])");

// 
// See if personal or global statistics should be viewed. We defaults
// to view the global section.
// 
if(!isset($_REQUEST['stat'])) {
    $_REQUEST['stat'] = "glob";
}

if($_REQUEST['stat'] == "glob" || $_REQUEST['stat'] == "load") {
    $GLOBALS['sect'] = "all";
}
else {
    $GLOBALS['sect'] = $GLOBALS['hostid'];
}

include "../template/standard.ui";

?>
