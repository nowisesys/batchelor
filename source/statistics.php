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
	    error_exit("invalid argument for request parameter $name");
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
// The root directory of hostid (that can be all).
// 
function stat_show_root($hostid)
{    
    $statdir = sprintf("%s/stat/%s", CACHE_DIRECTORY, $hostid);
    
    if(!file_exists($statdir)) {
        print_message_box("error", "statistics not found\n");
	return -1;
    }
}

// 
// Show statistics by year.
// 
function stat_show_year($hostid)
{
    $statdir = sprintf("%s/stat/%s/%s", CACHE_DIRECTORY, $hostid, $_REQUEST['year']);
    
    if(!file_exists($statdir)) {
        print_message_box("error", "statistics not found\n");
	return -1;
    }
}

// 
// Show statistics by month.
// 
function stat_show_month($hostid, $year, $month)
{
    $statdir = sprintf("%s/stat/%s/%s/%s", CACHE_DIRECTORY, $hostid, $_REQUEST['year'], $_REQUEST['month']);

    if(!file_exists($statdir)) {
        print_message_box("error", "statistics not found\n");
	return -1;
    }
}

// 
// Show statistics by day.
// 
function stat_show_day()
{
    $statdir = sprintf("%s/stat/%s/%s/%s/%s", CACHE_DIRECTORY, $hostid, $_REQUEST['year'], $_REQUEST['month'], $_REQUEST['day']);

    if(!file_exists($statdir)) {
        print_message_box("error", "statistics not found\n");
	return -1;
    }
}

// 
// Print the page body.
// 
function print_body()
{  
    printf("<h2><img src=\"icons/nuvola/statistics.png\"> Statistics</h2>\n");

    if(isset($GLOBALS['error'])) {
        print_message_box("error", $GLOBALS['error']);
	return;
    }
    
    if(isset($_REQUEST['year'])) {
	if(isset($_REQUEST['month'])) {
	    if(isset($_REQUEST['day'])) {
		stat_show_day($GLOBALS['sect']);
	    } 
	    else {
		stat_show_month($GLOBALS['sect']);
	    }
	} 
	else {
	    stat_show_year($GLOBALS['sect']);
	}
    } 
    else {
	stat_show_root($GLOBALS['sect']);
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
check_request_param("stat",  "(glob|pers)");
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

if($_REQUEST['stat'] == "glob") {
    $GLOBALS['sect'] = "all";
}
else {
    $GLOBALS['sect'] = $GLOBALS['hostid'];
}

include "../template/standard.ui";

?>
