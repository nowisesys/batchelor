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
// A simple script for starting a sequence processing job. If the request parameter
// file or seq is set, then we process the indata. The form for submitting jobs is
// always visible (we could redirect the browser to another page if we like.
// 

// 
// Include configuration and libs.
// 
include "../conf/config.inc";
include "../include/retrotector.inc";

function show_jobs_table(&$jobs)
{
    // 
    // Font colors:
    // 
    $color = array( "pending"  => "#000066",
		    "running"  => "#0000bb",
		    "finished" => "#006600",
		    "error"    => "#990000",
		    "crashed"  => "#666666" );
      
    print "<br><h3>Job queue:</h3>\n";
    print "<hr><table width=\"50%\"><tr><th>Started</th><th>Job</th><th>Status</th><th>Links</th></tr>\n";
    foreach($jobs as $jobdir => $job) {	    
	$label = sprintf("(%s)", $job['state']);
	switch($job['state']) {
	 case "pending":
	    $title = sprintf("queued %s, \nwaiting in queue", 
			     format_timestamp($job['queued'])); 
	    break;
	 case "running":
	    $title = sprintf("started %s, \nstill running", 
			     format_timestamp($job['started']));
	    break;
	 case "finished":
	    $title = sprintf("started %s, \nfinished %s", 
			     format_timestamp($job['started']), 
			     format_timestamp($job['finished']));
	    break;
	 case "error":
	    $title = sprintf("started %s, \nfinished with errors %s", 
			     format_timestamp($job['started']), 
			     format_timestamp($job['stderr']));
	    break;
	 case "crashed":
	    // $label = sprintf("%s (crashed)", format_timestamp($job['started']));
	    $title = sprintf("started %s, \njob has crashed (not running)", 
			     format_timestamp($job['started']));
	    break;
	}
	
	// 
	// Started column:
	// 
	if($job['state'] == "pending") {
	    print "<tr align=\"center\"><td align=\"center\">---</td>";
	}
	else {
	    printf("<tr align=\"center\"><td>%s</td>", format_timestamp($job['started']));
	}
	
	// 
	// Job column
	// 
	printf("<td><a href=\"details.php?jobid=%d&result=%s\" target=\"_blank\" title=\"%s\">Job %d</a></td>", 
	       $job['jobid'], $jobdir, $title, $job['jobid']);
	
	// 
	// Status column
	// 
	if($job['state'] == "running") {
	    printf("<td>==&gt; <font color=\"%s\">%s</font> &lt;==</td>", $color[$job['state']], $label);
	}
	else {
	    printf("<td><font color=\"%s\">%s</font></td>", $color[$job['state']], $label);
	}
	
	// 
	// Links column
	$links = array();
	if(SHOW_JOB_DELETE_LINK && $job['state'] != "running") {
	    array_push($links, sprintf("<a href=\"delete.php?jobid=%d&result=%s\">delete</a>", $job['jobid'], $jobdir));
	}
	if($job['state'] == "finished") {
	    array_push($links, sprintf("<a href=\"download.php?jobid=%d&result=%s\">download</a>", $job['jobid'], $jobdir));
	}
	printf("<td>%s</td></tr>\n", implode(", ", $links));
    }
    print "</table>\n";
}

function show_form($error = null)
{
    // 
    // Get array of all running and finished jobs for peer identified
    // by the hostid superglobal variable.
    // 
    $jobs = get_jobs($GLOBALS['hostid']);

    print "<html><head>\n";
    print "<title>Submit data for processing</title>\n";
    if(PAGE_REFRESH_RATE > 0) {
	// 
	// Only output meta refresh tag if we got pending 
	// or running jobs.
	// 
	foreach($jobs as $job) {
	    if($job['state'] == "pending" || $job['state'] == "running") {
		printf("<meta http-equiv=\"refresh\" content=\"%d\" />", PAGE_REFRESH_RATE);
		break;
	    }
	}
    }
    print "</head>\n";
    print "<body><h3>Submit data for processing</h3><hr>\n";

    // 
    // The form for uploading a file.
    // 
    print "<form enctype=\"multipart/form-data\" action=\"index.php\" method=\"POST\">\n";
    print "   <!-- MAX_FILE_SIZE must precede the file input field -->\n";
    print "   <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"30000\" />\n";
    print "   <!-- Name of input element determines name in \$_FILES array -->\n";
    print "   Process file: <input name=\"file\" type=\"file\" />\n";
    print "   <input type=\"submit\" value=\"Send File\" />\n";
    print "</form>\n";
    
    // 
    // The form for submitting a sequence.
    // 
    print "<form action=\"index.php\" method=\"GET\">\n";
    print "   Process sequence: <textarea name=\"seq\" cols=\"50\" rows=\"5\"></textarea>\n";
    print "   <input type=\"submit\" value=\"Send Sequence\" />\n";
    print "</form>\n";
    
    // 
    // Should we show an error message?
    //
    if(isset($error)) {
	printf("<hr><b>Error:</b> %s\n", $error);
    }

    // 
    // Show jobs.
    //
    if(count($jobs)) {
	show_jobs_table($jobs);
    }
    printf("<hr>Last updated: %s\n", format_timestamp(time()));
    
    print "</body></html>\n";
}

// 
// This function shows the form, including the form, and then
// terminates the script execution. A more polished alternative 
// to die().
//
function error_exit($str)
{
    show_form($str);
    exit(1);
}

// 
// Script execution starts here (main).
// 

// 
// Set cookie so we can associate peer with submitted, running
// and finished jobs.
// 
if(isset($_COOKIE['hostid'])) {
    $GLOBALS['hostid'] = $_COOKIE['hostid'];
}
else {
    $GLOBALS['hostid'] = md5($_SERVER['REMOTE_ADDR']);
    if(USE_SESSION_COOKIES) {
	// 
	// Set a session cookie.
	// 
	setcookie("hostid", $GLOBALS['hostid']);
    }
    else {
	// 
	// Set a persistent cookie.
	// 
	setcookie("hostid", $GLOBALS['hostid'], time() + COOKIE_LIFE_TIME);
    }
}

if(isset($_FILES['file']['name']) || isset($_REQUEST['seq'])) {
    // 
    // Create output and job spool directories.
    // 
    $jobdir = sprintf("%s/jobs/%s", CACHE_DIRECTORY, $GLOBALS["hostid"]);
    if(!file_exists($jobdir)) {
	if(!mkdir($jobdir, CACHE_PERMISSION, true)) {
	    error_exit("Failed create output directory");
	}
    }
    
    $jobdir = sprintf("%s/%d", $jobdir, time());
    if(!mkdir($jobdir, CACHE_PERMISSION, true)) {
	error_exit("Failed create output directory");
    }
    
    // 
    // Save peer <=> hostid mapping?
    //
    if(SAVE_HOSTID_MAPPING) {
	$mapdir = sprintf("%s/map", CACHE_DIRECTORY);
	save_hostid_mapping($mapdir, $GLOBALS['hostid'], $_SERVER['REMOTE_ADDR']);
    }
    
    // 
    // Create path to sequence data file.
    // 
    $seqfile = sprintf("%s/sequence", $jobdir);

    // 
    // Process request parameters.
    // 
    if(isset($_REQUEST['seq'])) {
	// 
	// Save the sequence to file.
	// 
	if(!file_put_contents($seqfile, $_REQUEST['seq'])) {
	    error_exit("Failed save sequence data to file");
	}
    }
    else {
	// 
	// Make sure the uploaded file is posted file and not an
	// system file, i.e. /etc/passwd
	// 
	if(is_uploaded_file($_FILES['file']['tmp_name'])) {
	    if(MIN_FILE_SIZE != 0 && filesize($_FILES['file']['tmp_name']) < MIN_FILE_SIZE) {
		unlink($_FILES['file']['tmp_name']);
		rmdir($jobdir);
		error_exit(sprintf("Uploaded file is too small (requires filesize >= %d bytes)", MIN_FILE_SIZE));
	    }
	    if(!rename($_FILES['file']['tmp_name'], $seqfile)) {
		unlink($_FILES['file']['tmp_name']);
		rmdir($jobdir);
		error_exit("Failed move uploaded sequence file");
	    }
	}
	else {
	    rmdir($jobdir);
	    error_exit("No uploaded file");
	}
    }
    
    // 
    // File uploaded or created. Now we just has to start the batch
    // job. The wrapper script path must be absolute. Create
    // 
    $resdir = sprintf("%s/result", $jobdir);
    if(!mkdir($resdir, CACHE_PERMISSION, true)) {
	error_exit("Failed create result directory");
    }
    $script = realpath(dirname(__FILE__) . "/../include/script.sh");
    $command = sprintf("%s %s %s %s", $script, $jobdir, $seqfile, $resdir);
    $job = run_process($command, $jobdir);
    
    // 
    // Save jobid and queued time to file in result dir.
    // 
    if(!file_put_contents(sprintf("%s/jobid", $jobdir), $job['jobid'])) {
	error_exit("Failed save jobid");
    }
    if(!file_put_contents(sprintf("%s/queued", $jobdir), time())) {
	error_exit("Failed save job enqueue time");
    }
    
    // 
    // Redirect the browser to an empty index.php to prevent page
    // update to submit the same sequence twice or more.
    // 
    header("Location: index.php");    
}

// 
// Show form and running and finished jobs.
// 
show_form();

?>
