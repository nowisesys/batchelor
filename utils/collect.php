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

if(USE_JPGRAPH_LIB) {
    include "../conf/jpgraph.inc";
    if(defined("JPGRAPH_LIB_PATH")) {
	ini_set("include_path", ini_get("include_path") . ":" . JPGRAPH_LIB_PATH);
    }
    foreach(array( "jpgraph.php", "jpgraph_bar.php", "jpgraph_line.php" ) as $jpgraph) {
	if(!@include($jpgraph)) {
	    die(sprintf("Failed include %s (see JPGRAPH_LIB_PATH inside conf/jpgraph.inc)\n", $jpgraph));
	}
    }
    if(!class_exists("Graph")) {
	die("Class Graph (from JpGraph) is undefined (check your JpGraph installation)\n");
    }
}

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
function collect_submit_count($hostid, &$data, $year, $month, $day, $hour)
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

    // 
    // Submit by hour:
    // 
    if(!isset($data[$hostid][$year][$month][$day][$hour]['submit']['count'])) {
	$data[$hostid][$year][$month][$day][$hour]['submit']['count'] = 0;
    }
    $data[$hostid][$year][$month][$day][$hour]['submit']['count']++;    
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
	$data[$hostid]['proctime']['waiting'] = 0;         // mean value of queued time
	$data[$hostid]['proctime']['running'] = 0;         // mean value of execution time
	$data[$hostid]['proctime']['count'] = 0;           // number of jobs
	$data[$hostid]['proctime']['minimum'] = $process;  // minimum time from submit to finished
	$data[$hostid]['proctime']['maximum'] = 0;         // maximum time from submit to finished
    }
    $data[$hostid]['proctime']['count']++;
    $data[$hostid]['proctime']['waiting'] = floating_mean_value($data[$hostid]['proctime']['count'],
								$data[$hostid]['proctime']['waiting'], $waiting);
    $data[$hostid]['proctime']['running'] = floating_mean_value($data[$hostid]['proctime']['count'],
								$data[$hostid]['proctime']['running'], $running);
    if($process < $data[$hostid]['proctime']['minimum']) {
	$data[$hostid]['proctime']['minimum'] = $process;
    }
    if($process > $data[$hostid]['proctime']['maximum']) {
	$data[$hostid]['proctime']['maximum'] = $process;
    }
    
    // 
    // Process accounting by year:
    // 
    if(!isset($data[$hostid][$year]['proctime'])) {
	$data[$hostid][$year]['proctime']['waiting'] = 0;         // mean value of queued time
	$data[$hostid][$year]['proctime']['running'] = 0;         // mean value of execution time
	$data[$hostid][$year]['proctime']['count'] = 0;           // number of jobs
	$data[$hostid][$year]['proctime']['minimum'] = $process;  // minimum time from submit to finished
	$data[$hostid][$year]['proctime']['maximum'] = 0;         // maximum time from submit to finished
    }
    $data[$hostid][$year]['proctime']['count']++;
    $data[$hostid][$year]['proctime']['waiting'] = floating_mean_value($data[$hostid][$year]['proctime']['count'],
								       $data[$hostid][$year]['proctime']['waiting'], $waiting);
    $data[$hostid][$year]['proctime']['running'] = floating_mean_value($data[$hostid][$year]['proctime']['count'],
								       $data[$hostid][$year]['proctime']['running'], $running);
    if($process < $data[$hostid][$year]['proctime']['minimum']) {
	$data[$hostid][$year]['proctime']['minimum'] = $process;
    }
    if($process > $data[$hostid][$year]['proctime']['maximum']) {
	$data[$hostid][$year]['proctime']['maximum'] = $process;
    }

    // 
    // Process accounting by month:
    // 
    if(!isset($data[$hostid][$year][$month]['proctime'])) {
	$data[$hostid][$year][$month]['proctime']['waiting'] = 0;          // mean value of queued time
	$data[$hostid][$year][$month]['proctime']['running'] = 0;          // mean value of execution time
	$data[$hostid][$year][$month]['proctime']['count'] = 0;            // number of jobs
	$data[$hostid][$year][$month]['proctime']['minimum'] = $process;   // minimum time from submit to finished
	$data[$hostid][$year][$month]['proctime']['maximum'] = 0;          // maximum time from submit to finished
    }
    $data[$hostid][$year][$month]['proctime']['count']++;
    $data[$hostid][$year][$month]['proctime']['waiting'] = floating_mean_value($data[$hostid][$year][$month]['proctime']['count'],
									       $data[$hostid][$year][$month]['proctime']['waiting'], $waiting);
    $data[$hostid][$year][$month]['proctime']['running'] = floating_mean_value($data[$hostid][$year][$month]['proctime']['count'],
									       $data[$hostid][$year][$month]['proctime']['running'], $running);
    if($process < $data[$hostid][$year][$month]['proctime']['minimum']) {
	$data[$hostid][$year][$month]['proctime']['minimum'] = $process;
    }
    if($process > $data[$hostid][$year][$month]['proctime']['maximum']) {
	$data[$hostid][$year][$month]['proctime']['maximum'] = $process;
    }

    // 
    // Process accounting by day:
    // 
    if(!isset($data[$hostid][$year][$month][$day]['proctime'])) {
	$data[$hostid][$year][$month][$day]['proctime']['waiting'] = 0;          // mean value of queued time
	$data[$hostid][$year][$month][$day]['proctime']['running'] = 0;          // mean value of execution time
	$data[$hostid][$year][$month][$day]['proctime']['count'] = 0;            // number of jobs
	$data[$hostid][$year][$month][$day]['proctime']['minimum'] = $process;   // minimum time from submit to finished
	$data[$hostid][$year][$month][$day]['proctime']['maximum'] = 0;          // maximum time from submit to finished
    }
    $data[$hostid][$year][$month][$day]['proctime']['count']++;
    $data[$hostid][$year][$month][$day]['proctime']['waiting'] = floating_mean_value($data[$hostid][$year][$month][$day]['proctime']['count'],
										     $data[$hostid][$year][$month][$day]['proctime']['waiting'], $waiting);
    $data[$hostid][$year][$month][$day]['proctime']['running'] = floating_mean_value($data[$hostid][$year][$month][$day]['proctime']['count'],
										     $data[$hostid][$year][$month][$day]['proctime']['running'], $running);
    if($process < $data[$hostid][$year][$month][$day]['proctime']['minimum']) {
	$data[$hostid][$year][$month][$day]['proctime']['minimum'] = $process;
    }
    if($process > $data[$hostid][$year][$month][$day]['proctime']['maximum']) {
	$data[$hostid][$year][$month][$day]['proctime']['maximum'] = $process;
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
		$hour  = sprintf("%02d", $date['hours']);
		
		// 
		// Save submit, state and process accounting statistics into array:
		// 
		collect_submit_count($hostid, $data, $year, $month, $day, $hour);
		collect_submit_count("all", $data, $year, $month, $day, $hour);
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

// 
// Recursive write data files (text) from collected statistics.
// 
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
// Draws a bar plot.
// 
function graph_draw_barplot($labels, $values, $image, $title, $subtitle, $bar)
{
    $width = 460;
    $height = 210;
    
    if(count($values) > 12) {
	$width += count($values) * 5;
    }
    
    // 
    // Create the graph and setup the basic parameters 
    // 
    $graph = new Graph($width, $height, 'auto');    
    $graph->img->SetMargin(40, 30, 40, 40);
    $graph->SetScale("textint");
    $graph->SetFrame(true, JPGRAPH_FRAME_FOREGROUND_COLOR, JPGRAPH_FRAME_BORDER_WIDTH); 
    $graph->SetColor(JPGRAPH_GRAPH_BACKGROUND_COLOR);
    $graph->SetMarginColor(JPGRAPH_FRAME_BACKGROUND_COLOR);
    
    // 
    // Add some grace to the top so that the scale doesn't
    // end exactly at the max value. 
    // 
    $graph->yaxis->scale->SetGrace(20);
    
    // 
    // Setup X-axis labels
    // 
    $graph->xaxis->SetTickLabels($labels);
    $graph->xaxis->SetFont(FF_FONT1, FS_BOLD);
    $graph->xaxis->SetColor(JPGRAPH_XAXIS_SCALE_COLOR, JPGRAPH_XAXIS_LABEL_COLOR);

    // 
    // Setup "hidden" y-axis by given it the same color
    // as the background
    // 
    $graph->yaxis->SetColor(JPGRAPH_YAXIS_SCALE_COLOR, JPGRAPH_YAXIS_LABEL_COLOR);
    $graph->ygrid->SetColor(JPGRAPH_GRAPH_FOREGROUND_COLOR);

    // 
    // Setup graph title ands fonts
    // 
    $graph->title->Set($title);
    $graph->title->SetColor(JPGRAPH_TITLE_MAIN_COLOR);
    $graph->title->SetFont(FF_FONT2, FS_BOLD);
    $graph->subtitle->Set(sprintf("(%s)", $subtitle));
    $graph->subtitle->SetColor(JPGRAPH_TITLE_SUBTITLE_COLOR);

    $graph->xaxis->title->Set(sprintf("Generated: %s", strftime("%G-%m-%d")));
    $graph->xaxis->title->SetFont(FF_FONT1, FS_NORMAL);
    $graph->xaxis->title->SetColor(JPGRAPH_NOTES_FOREGROUND_COLOR);
    
    // 
    // Create a bar pot
    // 
    $bplot = new BarPlot($values);    
    $bplot->SetFillGradient($bar['color']['start'], $bar['color']['end'], GRAD_HOR);
    $bplot->SetWidth(0.5);
    if(isset($bar['color']['outline'])) {
	$bplot->SetColor($bar['color']['outline']);
    }
    if(isset($bar['color']['shadow'])) {
	$bplot->SetShadow($bar['color']['shadow']);
    }
    
    // 
    // Setup the values that are displayed on top of each bar
    // 
    $bplot->value->Show();
    
    // 
    // Must use TTF fonts if we want text at an arbitrary angle
    // 
    $bplot->value->SetFont(FF_ARIAL, FS_NORMAL, 8);
    $bplot->value->SetFormat('%d');
    
    // 
    // Set colors for positive and negative values
    // 
    $bplot->value->SetColor($bar['text']['positive'], $bar['text']['negative']);
    $graph->Add($bplot);
    
    // 
    // Finally stroke the graph
    //
    $graph->Stroke($image);
}

// 
// Create graph of total submits for hostid (that might be all)
// for all years.
// 
function graph_total_submit($graphdir, $hostid, $options, $data)
{
    $image  = sprintf("%s/submit.png", $graphdir);
    $values = array();
    $labels = array();
    $title  = "Total number of submits";
    $total  = 0;
    $barcol = array( "color" => array( "start"    => "navy",
				       "end"      => "lightsteelblue",
				       "outline"  => "darkblue" ),
		     "text"  => array( "positive" => "black",
				       "negative" => "lightgray" )
		     );
    
    foreach($data as $year => $data1) {
	if(is_numeric($year)) {
	    foreach($data1 as $sect => $value) {
		if($sect == "submit") {
		    array_push($values, $value['count']);
		    array_push($labels, $year);
		    $total += $value['count'];
		}
	    }
	}
    }
    
    // 
    // Add one extra year before first year and after last year to prevent
    // odd looking graphics when only one year statistics is present.
    // 
    if(count($labels) < 3) {
	array_unshift($values, 0);
	array_unshift($labels, $labels[0] - 1);
	array_push($values, 0);
	array_push($labels, $labels[count($labels) - 1] + 1);
    }
    
    if($options->debug) {
	printf("debug: creating graphic file %s\n", $image);
    }
    graph_draw_barplot($labels, $values, $image, $title, sprintf("total %d submits", $total), $barcol);
}

// 
// Create graph of submits for one year for hostid (that might be all)
// 
function graph_yearly_submit($graphdir, $hostid, $options, $timestamp, $data)
{
    $image  = sprintf("%s/submit.png", $graphdir);
    $values = array();
    $labels = array();
    $title  = sprintf("Number of submits %s", strftime("%G", $timestamp));
    $total  = 0;
    $barcol = array( "color" => array( "start"    => "orange",
				       "end"      => "yellow",
				       "outline"  => "red" ),
		     "text"  => array( "positive" => "black",
				       "negative" => "lightgray" )
		     );

    // 
    // Initilize data.
    // 
    for($i = 0; $i < 12; ++$i) {
	$values[$i] = 0;
	$labels[$i] = strftime("%b", mktime(0, 0, 0, $i + 1, 1, 1));
    }
    
    foreach($data as $month => $data1) {
	if(is_numeric($month)) {
	    foreach($data1 as $sect => $value) {
		if($sect == "submit") {
		    $values[intval($month) - 1] = $value['count'];
		    $total += $value['count'];
		}
	    }
	}
    }

    if($options->debug) {
	printf("debug: creating graphic file %s\n", $image);	
    }
    graph_draw_barplot($labels, $values, $image, $title, sprintf("total %d submits", $total), $barcol);
}

// 
// Create graph of submits for one month for hostid (that might be all)
// 
function graph_monthly_submit($graphdir, $hostid, $options, $timestamp, $data)
{
    $image  = sprintf("%s/submit.png", $graphdir);
    $values = array();
    $labels = array();
    $title  = sprintf("Number of submits for %s", strftime("%B %G", $timestamp));
    $total  = 0;
    $barcol = array( "color" => array( "start"    => "green",
				       "end"      => "yellow",
				       "outline"  => "darkgreen" ),
		     "text"  => array( "positive" => "black",
				       "negative" => "lightgray" )
		     );

    // 
    // Initilize data.
    // TODO: This depends on number of days in this month!
    // 
    for($i = 0; $i < 31; ++$i) {
	$values[$i] = 0;
	$labels[$i] = $i + 1;
    }
    
    foreach($data as $day => $data1) {
	if(is_numeric($day)) {
	    foreach($data1 as $sect => $value) {
		if($sect == "submit") {
		    $values[intval($day) - 1] = $value['count'];
		    $total += $value['count'];
		}
	    }
	}
    }

    if($options->debug) {
	printf("debug: creating graphic file %s\n", $image);	
    }
    graph_draw_barplot($labels, $values, $image, $title, sprintf("total %d submits", $total), $barcol);
}

// 
// Create graph of submits for one day for hostid (that might be all)
// 
function graph_daily_submit($graphdir, $hostid, $options, $timestamp, $data)
{
    $image  = sprintf("%s/submit.png", $graphdir);
    $values = array();
    $labels = array();
    $title  = sprintf("Number of submits for %s", strftime("%G-%m-%d", $timestamp));
    $total  = 0;
    $barcol = array( "color" => array( "start"    => "purple",
				       "end"      => "red",
				       "outline"  => "pink" ),
		     "text"  => array( "positive" => "black",
				       "negative" => "lightgray" )
		     );

    // 
    // Initilize data.
    // 
    for($i = 0; $i < 23; ++$i) {
	$values[$i] = 0;
	$labels[$i] = $i + 1;
    }
    
    foreach($data as $hour => $data1) {
	if(is_numeric($hour)) {
	    foreach($data1 as $sect => $value) {
		if($sect == "submit") {
		    $values[intval($hour) - 1] = $value['count'];
		    $total += $value['count'];
		}
	    }
	}
    }

    if($options->debug) {
	printf("debug: creating graphic file %s\n", $image);	
    }
    graph_draw_barplot($labels, $values, $image, $title, sprintf("total %d submits", $total), $barcol);
}

// 
// Draws a diagram of process time data.
// 
function graph_draw_proctime($labels, $values, $image, $title, $subtitle, $barcol)
{
    $width = 560;
    $height = 210;

    if(count($labels) > 5) {
	$width += count($labels) * 10;
    }
    
    // 
    // The zeroes array is used as a placeholder when grouping bars.
    // 
    $zeroes = array();
    for($i = 0; $i < count($labels); ++$i) {
	array_push($zeroes, 0);
    }

    // 
    // Create the graph.
    // 
    $graph = new Graph($width, $height, 'auto');           
    $graph->SetScale("textlin");
    $graph->SetY2Scale("lin");
    $graph->img->SetMargin(40, 150, 20, 40);        
    $graph->SetFrame(true, JPGRAPH_FRAME_FOREGROUND_COLOR, JPGRAPH_FRAME_BORDER_WIDTH); 
    $graph->SetColor(JPGRAPH_GRAPH_BACKGROUND_COLOR);
    $graph->SetMarginColor(JPGRAPH_FRAME_BACKGROUND_COLOR);
    $graph->legend->Pos(0.03, 0.1);
    
    // 
    // Add some grace to the top so that the scale doesn't
    // end exactly at the max value. 
    // 
    $graph->yaxis->scale->SetGrace(10);
    $graph->y2axis->scale->SetGrace(20);
    
    // 
    // Setup X-axis labels
    // 
    $graph->xaxis->SetTickLabels($labels);
    $graph->xaxis->SetFont(FF_FONT1, FS_BOLD);
    $graph->xaxis->SetColor(JPGRAPH_XAXIS_SCALE_COLOR, JPGRAPH_XAXIS_LABEL_COLOR);

    // 
    // Setup Y-axis labels
    // 
    $graph->yaxis->SetColor(JPGRAPH_YAXIS_SCALE_COLOR, JPGRAPH_YAXIS_LABEL_COLOR);
    $graph->y2axis->SetColor(JPGRAPH_YAXIS_SCALE_COLOR, JPGRAPH_YAXIS_LABEL_COLOR);
    $graph->ygrid->SetColor(JPGRAPH_GRAPH_FOREGROUND_COLOR);
    $graph->yaxis->title->Set("Running / Waiting (seconds)");
    $graph->y2axis->title->Set("Minimum / Maximum (seconds)");

    // 
    // Setup graph title ands fonts
    // 
    $graph->title->Set($title);
    $graph->title->SetColor(JPGRAPH_TITLE_MAIN_COLOR);
    $graph->title->SetFont(FF_FONT2, FS_BOLD);
    $graph->subtitle->Set(sprintf("(%s)", $subtitle));
    $graph->subtitle->SetColor(JPGRAPH_TITLE_SUBTITLE_COLOR);

    $graph->xaxis->title->Set(sprintf("Generated: %s", strftime("%G-%m-%d")));
    $graph->xaxis->title->SetFont(FF_FONT1, FS_NORMAL);
    $graph->xaxis->title->SetColor(JPGRAPH_NOTES_FOREGROUND_COLOR);
    
    // 
    // Create the bar and line plots
    // 
    $b1plot = new BarPlot($values['waiting']);
    $b1plot->SetFillColor($barcol['color']['waiting']);
    $b1plot->Setlegend("Waiting");

    $b2plot = new BarPlot($values['running']);
    $b2plot->SetFillColor($barcol['color']['running']);
    $b2plot->SetLegend("Running");

    $b3plot = new BarPlot($values['maximum']);
    $b3plot->SetFillColor($barcol['color']['maximum']);
    $b3plot->SetLegend("Maximum");

    $b4plot = new BarPlot($values['minimum']);
    $b4plot->SetFillColor($barcol['color']['minimum']);
    $b4plot->SetLegend("Minimum");

    $z0plot = new BarPlot($zeroes);
    
    // 
    // Create the grouped bar plot and add it to the graph:
    // 
    $abplot = new AccBarPlot(array($b1plot, $b2plot));
    $abplot->value->show();
    $abplot->value->SetColor($barcol['text']['positive'], $barcol['text']['negative']);

    $y1plot = new GroupBarPlot(array($abplot, $z0plot, $z0plot));
    $y2plot = new GroupBarPlot(array($z0plot, $b3plot, $b4plot));
    $b3plot->value->show();
    $b4plot->value->show();
    $b3plot->value->SetColor($barcol['text']['positive'], $barcol['text']['negative']);
    $b4plot->value->SetColor($barcol['text']['positive'], $barcol['text']['negative']);
    
    $graph->Add($y1plot);
    $graph->AddY2($y2plot);
    
    // 
    // Create the graph
    // 
    $graph->Stroke($image);
}

// 
// Create graph of total process time for hostid (that might be all)
// for all years.
// 
function graph_total_proctime($graphdir, $hostid, $options, $data)
{
    $image  = sprintf("%s/proctime.png", $graphdir);
    $values = array( "waiting" => array(), 
		     "running" => array(), 
		     "count"   => array(), 
		     "minimum" => array(), 
		     "maximum" => array());
    $labels = array();
    $title  = "Total process time (avarage)";
    $total  = 0;
    $barcol = array( "color" => array( "waiting"  => "yellow",
				       "running"  => "green",
				       "minimum"  => "lightgray",
				       "maximum"  => "red", 
				       "count"    => "darkblue" ),		     
		     "text"  => array( "positive" => "black",
				       "negative" => "lightgray" )
		     );
    
    foreach($data as $year => $data1) {
	if(is_numeric($year)) {
	    foreach($data1 as $sect => $value) {
		if($sect == "proctime") {
		    foreach(array_keys($values) as $key) {
			array_push($values[$key], $value[$key]);
		    }
		    array_push($labels, $year);
		    $total += $value['count'];
		}
	    }
	}
    }
    
    // 
    // Add one extra year before first year and after last year to prevent
    // odd looking graphics when only one year statistics is present.
    // 
    if(count($labels) < 3) {
	foreach(array_keys($values) as $key) {
	    array_unshift($values[$key], 0);
	    array_push($values[$key], 0);
	}
	array_unshift($labels, $labels[0] - 1);
	array_push($labels, $labels[count($labels) - 1] + 1);
    }
    
    if($options->debug) {
	printf("debug: creating graphic file %s\n", $image);
    }
    graph_draw_proctime($labels, $values, $image, $title, sprintf("totally %d finished jobs counted", $total), $barcol);
}

// 
// Create graph of process time for one year for hostid (that might be all)
// 
function graph_yearly_proctime($graphdir, $hostid, $options, $timestamp, $data)
{
    $image  = sprintf("%s/proctime.png", $graphdir);
    $values = array( "waiting" => array(), 
		     "running" => array(), 
		     "count"   => array(), 
		     "minimum" => array(), 
		     "maximum" => array());
    $labels = array();
    $title  = sprintf("Process time %s (avarage)", strftime("%G", $timestamp));
    $total  = 0;
    $barcol = array( "color" => array( "waiting"  => "yellow",
				       "running"  => "green",
				       "minimum"  => "lightgray",
				       "maximum"  => "red", 
				       "count"    => "darkblue" ),		     
		     "text"  => array( "positive" => "black",
				       "negative" => "lightgray" )
		     );

    // 
    // Initilize data.
    // 
    for($i = 0; $i < 12; ++$i) {
	foreach(array_keys($values) as $key) {
	    $values[$key][$i] = 0;
	}
	$labels[$i] = strftime("%b", mktime(0, 0, 0, $i + 1, 1, 1));
    }
    
    foreach($data as $month => $data1) {
	if(is_numeric($month)) {
	    foreach($data1 as $sect => $value) {
		if($sect == "proctime") {
		    $index = intval($month) - 1;
		    foreach(array_keys($values) as $key) {
			$values[$key][$index] = $value[$key];
		    }
		    $total += $value['count'];
		}
	    }
	}
    }
    
    if($options->debug) {
	printf("debug: creating graphic file %s\n", $image);
    }
    graph_draw_proctime($labels, $values, $image, $title, sprintf("totally %d finished jobs counted", $total), $barcol);
}

// 
// Create graph of process time for one month for hostid (that might be all)
// 
function graph_monthly_proctime($graphdir, $hostid, $options, $timestamp, $data)
{
}

// 
// Create graph of process time for one day for hostid (that might be all)
// 
function graph_daily_proctime($graphdir, $hostid, $options, $timestamp, $data)
{
}

// 
// Generate graphics from collected statistics.
// 
function collect_flush_graphics($statdir, $data, $options)
{
    if(!USE_JPGRAPH_LIB) {
	if($options->debug) {
	    printf("debug: not generating graphics from statistics (disabled in config.inc)\n");
	    return;
	}
    }
    
    // 
    // TODO: add call of user supplied hook functions.
    // 
    foreach($data as $hostid => $data1) {              // total level
	$graphdir = sprintf("%s/%s", $statdir, $hostid);
	graph_total_submit($graphdir, $hostid, $options, $data1);
	graph_total_proctime($graphdir, $hostid, $options, $data1);
	foreach($data1 as $sect1 => $data2) {          // year level
	    if(!is_numeric($sect1)) {
		continue;
	    }
	
	    $graphdir = sprintf("%s/%s/%s", $statdir, $hostid, $sect1);
	    $datetime = mktime(0, 0, 0, 1, 1, $sect1);
	    if($options->debug) {
		printf("debug: generate yearly (%s) graphics:\n", strftime("%G-%m-%d", $datetime));
		printf("debug:   hostid = %s\n", $hostid);
		printf("debug:   resdir = %s\n", $graphdir);
	    }
	    graph_yearly_submit($graphdir, $hostid, $options, $datetime, $data2);
	    graph_yearly_proctime($graphdir, $hostid, $options, $datetime, $data2);
		
	    foreach($data2 as $sect2 => $data3) {      // month level
		if(!is_numeric($sect2)) {
		    continue;
		}
		
		$graphdir = sprintf("%s/%s/%s/%s", $statdir, $hostid, $sect1, $sect2);
		$datetime = mktime(0, 0, 0, $sect2, 1, $sect1);
		if($options->debug) {
		    printf("debug: generate monthly (%s) graphics:\n", 
			   strftime("%G-%m-%d", $datetime));
		    printf("debug:   hostid = %s\n", $hostid);
		    printf("debug:   resdir = %s\n", $graphdir);
		}
		graph_monthly_submit($graphdir, $hostid, $options, $datetime, $data3);
		graph_monthly_proctime($graphdir, $hostid, $options, $datetime, $data3);

		foreach($data3 as $sect3 => $data4) {  // day level
		    if(!is_numeric($sect3)) {
			continue;
		    }
		
		    $graphdir = sprintf("%s/%s/%s/%s/%s", $statdir, $hostid, $sect1, $sect2, $sect3);
		    $datetime = mktime(0, 0, 0, $sect2, $sect3, $sect1);
		    if($options->debug) {
			printf("debug: generate daily (%s) graphics:\n", 
			       strftime("%G-%m-%d", $datetime));
			printf("debug:   hostid = %s\n", $hostid);
			printf("debug:   resdir = %s\n", $graphdir);
		    }
		    graph_daily_submit($graphdir, $hostid, $options, $datetime, $data4);
		    graph_daily_proctime($graphdir, $hostid, $options, $datetime, $data4);
		}
	    }
	}
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
    if(!file_exists($statdir)) {
	if($options->debug) {
	    printf("debug: creating statistics directory '%s'\n", $statdir);
	}
	if(!mkdir($statdir)) {
	    die(sprintf("%s: failed create directory '%s'\n", $options->prog, $statdir));
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
	if($options->verbose) {
	    print_r($statdata);
	}
    }
    file_put_contents($statfile, serialize($statdata));

    // 
    // Flush collected statistics to filesystem:
    // 
    collect_flush_stats($statdir, $statdata, $options);
    collect_flush_graphics($statdir, $statdata, $options);
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
