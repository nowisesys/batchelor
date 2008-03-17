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
// This is an example of using hooks to modify the behavour of batchelor.
// If you want to try it out, copy it to include/hooks.inc.
// 
// These functions uses the remote peers ip-address to perform access 
// restrictions, collection and presentation of hosts that has used the
// job queue.
// 

// 
// Validate peer against array of hosts allowed to submit jobs.
// 
function pre_enqueue_hook($file, &$error)
{
    $allowed = array( "127.0.0.1", "192.168.45.8" );
    
    $found = false;
    foreach($allowed as $addr) {
	if($_SERVER['REMOTE_ADDRESS'] == $addr) {
	    $found = true;
	}
    }
    if(!$found) {
	$error = "You are not allowed to submit jobs";
	return false;
    }
    
    return true;
}

// 
// Save a file with ip-address of submitter.
// 
function post_enqueue_hook($indata, $jobdir)
{
    file_put_contents(sprintf("%s/ipaddr", $jobdir), $_SERVER['REMOTE_ADDR']);
}

// 
// Count number of submits per host. For clearity, we only show how to do this
// for total, yearly and montly.
// 
function collect_data_hook($hostid, &$data, $jobdir, $year, $month, $day, $hour)
{
    // 
    // Using $hostid != "all" is pointless in this example, just ignore it.
    // 
    if($hostid != "all") {
	return;
    }

    // 
    // Define out section.
    // 
    $sect = "ipaddr";
    $addr = file_get_contents(sprintf("%s/ipaddr", $jobdir));
    
    // 
    // Make sure to initilize our section on first use.
    // 
    if(!isset($data[$hostid][$sect][$addr])) {
	$data[$hostid][$sect][$addr] = 0;
    }
    if(!isset($data[$hostid][$year][$sect][$addr])) {
	$data[$hostid][$year][$sect][$addr] = 0;
    }
    if(!isset($data[$hostid][$year][$month][$sect][$addr])) {
	$data[$hostid][$year][$month][$sect][$addr] = 0;
    }
    
    // 
    // Count this host:
    // 
    $data[$hostid][$sect][$addr]++;
    $data[$hostid][$year][$sect][$addr]++;
    $data[$hostid][$year][$month][$sect][$addr]++;
}

// 
// Generate graphics from data collected by collect_data_hook():
// 
function graph_data_hook($graphdir, $hostid, $options, $datetime, $data, $type)
{
}

?>
