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
// The collected data gets saved to summary.dat as a list of ip-addresses
// and the submit frequence for this date (all, year or mounth):
// [ipaddr]
// 192.168.56.7 = 16
// 192.168.45.9 = 10
// 169.254.37.2 = 5
// 192.168.56.3 = 12
// 

// 
// Add some bogus ip-addresses for testing?
// 
define ("HOOKS_ADD_BOGUS_IPADDR", false);

// 
// Validate peer against array of hosts allowed to submit jobs.
// 
function pre_enqueue_hook(&$file, $jobdir, &$handled, &$jobs)
{
    $allowed = array( "127.0.0.1", "192.168.45.8" );
    
    foreach($allowed as $addr) {
	if($_SERVER['REMOTE_ADDR'] == $addr) {
	    return true;
	}
    }

    put_error("You are not allowed to submit jobs");
    return false;
}

// 
// Save a file with ip-address of submitter.
// 
function post_enqueue_hook($file, $jobdir)
{
    file_put_contents(sprintf("%s/ipaddr", $jobdir), $_SERVER['REMOTE_ADDR']);
}

// 
// Count number of submits per host. For clearity, we only show how to do this
// for total, yearly and monthly.
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
    // The file to collect and the section name we save under.
    // 
    $sect = "ipaddr";
    $file = sprintf("%s/ipaddr", $jobdir);

    // 
    // This will make sure a ipaddr file exists.
    // 
    if(HOOKS_ADD_BOGUS_IPADDR) {
	file_put_contents($file, sprintf("192.168.32.%s", rand(2, 32)));
    }
    
    if(file_exists($file)) {
	$addr = file_get_contents($file);
   
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
}

// 
// Generate graphics from data collected by collect_data_hook():
// 
function graph_data_hook($graphdir, $hostid, $options, $datetime, $data, $type)
{
    $image  = sprintf("%s/ipaddr.png", $graphdir);
    $values = array();
    $labels = array();
    $total  = 0;
    $hosts  = 0;
    $colors = array( "color" => array( "start"    => "red",
				       "end"      => "orange",
				       "outline"  => "darkred" ),
		     "text"  => array( "positive" => "black",
				       "negative" => "lightgray" )
		     );
 
    // 
    // Make sure the ipaddr section is defined.
    // 
    if(!isset($data['ipaddr'])) {
	return;
    }
    
    foreach($data['ipaddr'] as $addr => $count) {
	$total += $count;
	array_push($values, $count);
	array_push($labels, $addr);
    }
    $hosts = count($data['ipaddr']);

    switch($type) {
     case "total":
	$title = "Total queued jobs per host";
	break;
     case "yearly":
	$title = sprintf("Queued jobs per host for year %s", date('Y', $datetime));
	break;
     case "monthly":
	$title = sprintf("Queued jobs per host for %s", date('F Y', $datetime));
	break;
     case "daily":
	$title = sprintf("Queued jobs per host %s", date('Y-m-d', $datetime));
	break;
    }
    if(HOOKS_ADD_BOGUS_IPADDR) {
	$title .= " (using bogus ip-addresses)";
    }
    $subtitle = sprintf("total %d queued jobs counted, grouped on %d hosts IP-address", $total, $hosts);

    if($options->debug) {
	printf("debug: creating graphic file %s\n", $image);
    }
    graph_draw_barplot($labels, $values, $image, $title, $subtitle, $colors, "graph_data_hook_callback");
}

// 
// This is a callback for fine-tuning the bar plot graph.
// 
function graph_data_hook_callback(&$graph)
{
    $graph->img->SetMargin(40, 30, 40, 75);
    $graph->xaxis->SetFont(FF_ARIAL, FS_NORMAL, 7);
    $graph->xaxis->SetLabelAngle(45);
}

// 
// This function gets called to present the collected data and
// related graphics.
// 
function show_data_hook($data, $statdir, $subsect)
{
    if(isset($data['ipaddr'])) {
	$hosts = count($data['ipaddr']);
	$total = 0;
	$topnum = 0;
	foreach($data['ipaddr'] as $host => $count) {
	    if($count > $topnum) {
		$tophost = $host;
		$topnum  = $count;
	    }
	    $total += $count;
	}

	printf("<span id=\"secthead\">Queued jobs:</span>\n");

	printf("<p>%d hosts has queued a total of %d jobs. The top user of the queue is <b>%s</b><br> with a total of %d enqueued jobs.</p>\n", 
	       $hosts, $total, $tophost, $topnum);
	
	printf("<p>\n");
	if(file_exists(sprintf("%s/ipaddr.png", $statdir))) {
	    printf("<img src=\"image.php?%s\">\n", 
		   request_params(array( "image" => "ipaddr" )));
	}
	printf("</p>\n");
    }
}

?>
