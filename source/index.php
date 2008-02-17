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
// A simple script for starting a batch job. If the request parameter file or data
// is set, then we save it and process the indata. The form for submitting jobs and
// the queue view is always visible.
// 
// The code is a bit messy because we use interface templates (with callbacks), 
// meta-refresh and reports errors using the same function as prints the page.
// 

// 
// Include configuration and libs.
// 
include "../conf/config.inc";
include "../include/common.inc";
include "../include/ui.inc";

// 
// The array of pending and running jobs.
// 
// $jobs = null;

function print_select($label, $name, $values)
{
    if(isset($_REQUEST[$name])) {
	$selected = $_REQUEST[$name];
    }
    printf("<td>%s:&nbsp;<select name=\"%s\">\n", $label, $name);
    foreach($values as $key => $val) {
	if(isset($selected) && $val == $selected) {
	    printf("<option value=\"%s\" selected>%s</option>\n", $val, $key);
	}
	else {
	    printf("<option value=\"%s\">%s</option>\n", $val, $key);
	}
    }
    print "</select></td>\n";
}

// 
// 
function show_jobs_table(&$jobs)
{
    print "<br><table><tr><td><span id=\"secthead\">Job queue:</span></td>\n";
    print "<td><form action=\"index.php\" method=\"get\">\n";
    print "<input type=\"hidden\" name=\"show\" value=\"queue\" />\n";
    print "<table><tr>\n";
    print_select("Sort on", "sort", array( "None" => "none", "Started" => "started", 
					   "Job ID" => "jobid", "Status" => "state" )); 
    print_select("Show", "filter",  array( "All" => "all", "Pending" => "pending", 
					   "Running" => "running", "Finished" => "finished", 
					   "Error" => "error", "Crashed" => "crashed" ));
    print "<td><input type=\"submit\" value=\"Refresh\"></td>\n";
    print "</tr></table></form></td></tr></table>\n";
    if(count($jobs)) {	
	if(USE_ICONIZED_QUEUE) {
	    show_jobs_table_icons($jobs);
	}
	else {
	    show_jobs_table_plain($jobs);
	}
    }
}

function show_jobs_table_icons(&$jobs)
{
    print "<div class=\"indent\"><table width=\"50%\"><tr><th>Queued</th><th>Finished</th><th>Started</th><th>Job</th><th>Download</th><th>Delete</th></tr>\n";
    foreach($jobs as $jobdir => $job) {	    
	// $label = sprintf("(%s)", $job['state']);
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
	    $title = sprintf("started %s, \njob has crashed (not running)", 
			     format_timestamp($job['started']));
	    break;
	}
	
	// 
	// The queued and status column.
	// 
	if($job['state'] == "running" || $job['state'] == "pending") {
	    printf("<tr align=\"right\"><td><img src=\"icons/nuvola/%s.png\" alt=\"%s\"></td><td>&nbsp;</td>", $job['state'], $job['state']);
	}
	else {	    
	    printf("<tr align=\"right\"><td>&nbsp;</td><td><img src=\"icons/nuvola/%s.png\" alt=\"%s\"></td>", $job['state'], $job['state']);
	}
	
	// 
	// Finished jobs column:
	// 
	if($job['state'] == "pending") {
	    print "<td align=\"center\">---</td>";
	}
	else {
	    printf("<td nowrap>%s</td>", format_timestamp($job['started']));
	}
	
	// 
	// Job column
	// 
	printf("<td nowrap><a href=\"details.php?jobid=%d&result=%s\" target=\"_blank\" title=\"%s\">Job %d</a></td>", 
	       $job['jobid'], $jobdir, $title, $job['jobid']);
		
	// 
	// Download and delete column
	// 
	if($job['state'] == "finished") {
	    printf("<td><a href=\"download.php?jobid=%d&result=%s\" title=\"download result\"><img src=\"icons/nuvola/download.png\" alt=\"download\"></a></td>", $job['jobid'], $jobdir);
	}
	else {
	    printf("<td>&nbsp;</td>\n");
	}
	if(SHOW_JOB_DELETE_LINK && $job['state'] != "running") {
	    printf("<td nowrap><a href=\"delete.php?jobid=%d&result=%s\" title=\"delete job\"><img src=\"icons/nuvola/delete.png\" alt=\"delete\"></a></td></tr>", $job['jobid'], $jobdir);
	}
    }
    print "</table></div>\n";
}

function show_jobs_table_plain(&$jobs)
{
    // 
    // Font colors:
    // 
    $color = array( "pending"  => "#000066",
		    "running"  => "#0000bb",
		    "finished" => "#006600",
		    "error"    => "#990000",
		    "crashed"  => "#666666" );
      
    print "<div class=\"indent\"><table width=\"50%\"><tr><th>Started</th><th>Job</th><th>Status</th><th>Links</th></tr>\n";
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
	    printf("<tr align=\"center\"><td nowrap>%s</td>", format_timestamp($job['started']));
	}
	
	// 
	// Job column
	// 
	printf("<td nowrap><a href=\"details.php?jobid=%d&result=%s\" target=\"_blank\" title=\"%s\">Job %d</a></td>", 
	       $job['jobid'], $jobdir, $title, $job['jobid']);
	
	// 
	// Status column
	// 
	if($job['state'] == "running") {
	    printf("<td nowrap>==&gt; <font color=\"%s\">%s</font> &lt;==</td>", $color[$job['state']], $label);
	}
	else {
	    printf("<td nowrap><font color=\"%s\">%s</font></td>", $color[$job['state']], $label);
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
	printf("<td nowrap>%s</td></tr>\n", implode(", ", $links));
    }
    print "</table></div>\n";
}

// 
// This function prints the page body.
// 
function print_body()
{
    global $jobs;

    if(!isset($_REQUEST['show'])) {	
	$_REQUEST['show'] = "submit";
    }

    if($_REQUEST['show'] == "submit") {
	print "<br><span id=\"secthead\">Submit data for processing:</span><br><br>\n";

	// 
	// The form for uploading a file.
	// 
	print "<form enctype=\"multipart/form-data\" action=\"index.php\" method=\"POST\">\n";
	if(UPLOAD_MAX_FILESIZE > 0) {
	    print "   <!-- MAX_FILE_SIZE must precede the file input field -->\n";
	    printf("   <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"%d\" />\n", UPLOAD_MAX_FILESIZE);
	}
	if(isset($_REQUEST['sort'])) {
	    printf("   <input type=\"hidden\" name=\"sort\" value=\"%s\" />\n", $_REQUEST['sort']);
	}
	if(isset($_REQUEST['filter'])) {
	    printf("   <input type=\"hidden\" name=\"filter\" value=\"%s\" />\n", $_REQUEST['filter']);
	}
	
	print "   <!-- Name of input element determines name in \$_FILES array -->\n";
	print "   Process file: <input name=\"file\" type=\"file\" />\n";
	print "   <input type=\"submit\" value=\"Send File\" />\n";
	print "</form>\n";
	
	// 
	// The form for submitting a data.
	// 
	print "<form action=\"index.php\" method=\"GET\">\n";
	print "   Process data: <textarea name=\"data\" cols=\"50\" rows=\"5\"></textarea>\n";
	print "   <input type=\"submit\" value=\"Send Data\" />\n";
	if(isset($_REQUEST['sort'])) {
	    printf("   <input type=\"hidden\" name=\"sort\" value=\"%s\" />\n", $_REQUEST['sort']);
	}
	if(isset($_REQUEST['filter'])) {
	    printf("   <input type=\"hidden\" name=\"filter\" value=\"%s\" />\n", $_REQUEST['filter']);
	}
	print "</form>\n";
    }
    
    // 
    // Should we show an error message?
    //
    if(isset($GLOBALS['error'])) {
	printf("<div id=\"info\"><table><tr><td><img src=\"icons/nuvola/warning.png\"></td><td valign=\"top\">%s</td></tr></table></div>", $GLOBALS['error']);
    }

    if($_REQUEST['show'] == "queue") {
	// 
	// Show jobs.
	//
	show_jobs_table($jobs);
    }
}

// 
// The output callback used by interface template.
// 
function print_html($what)
{
    switch($what) {
     case "body":
	print_body();
	break;
     case "title":
	print "Submit data for processing";
	break;
     default:
	print_common_html($what);    // Use default callback.
	break;
    }
}

// 
// This is where we start output the whole page.
// 
function show_page($error = null) 
{
    global $jobs;
    
    if(isset($error)) {
	$GLOBALS['error'] = $error;
    }
        
    // 
    // Get array of all running and finished jobs for peer identified
    // by the hostid superglobal variable.
    // 
    $jobs = get_jobs($GLOBALS['hostid'], $_REQUEST['sort'], $_REQUEST['filter']);
    
    if(PAGE_REFRESH_RATE > 0) {
	// 
	// Only output meta refresh tag if we got pending 
	// or running jobs.
	// 
	foreach($jobs as $job) {
	    if($job['state'] == "pending" || $job['state'] == "running") {
		$GLOBALS['refresh'] = true;
		break;
	    }
	}
    }
    
    include "../template/standard.ui";
}

// 
// This function shows the form, including the form, and then
// terminates the script execution. A more polished alternative 
// to die().
//
function error_exit($str)
{
    show_page($str);
    exit(1);
}

// 
// This function should be called prior to error_exit() to
// clean the job directory on failure.
// 
function cleanup_jobdir($jobdir, $indata = null)
{
    if(isset($indata)) {
	if(file_exists($indata)) {
	    if(!unlink($indata)) {
		error_exit("Failed cleanup job directory");
	    }
	}
    }
    if(file_exists($jobdir)) {
	if(!rmdir($jobdir)) {
	    error_exit("Failed cleanup job directory");
	}
    }
}

// 
// Validate request parameter.
// 
function check_request_param($name, $accepted)
{
    if(!in_array($_REQUEST[$name], $accepted)) {
	error_exit(sprintf("Invalid value '%s' for request parameter '%s'", 
			   $_REQUEST[$name], $name));
    }
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

if(isset($_FILES['file']['name']) || isset($_REQUEST['data'])) {
    // 
    // Create output and job spool directories.
    // 
    $jobdir = sprintf("%s/jobs/%s", CACHE_DIRECTORY, $GLOBALS["hostid"]);
    if(!file_exists($jobdir)) {
	if(!create_directory($jobdir, CACHE_PERMISSION, true)) {
	    error_exit("Failed create output directory");
	}
    }
    
    $jobdir = sprintf("%s/%d", $jobdir, time());
    if(!create_directory($jobdir, CACHE_PERMISSION, true)) {
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
    // Create path to indata file.
    // 
    $indata = sprintf("%s/indata", $jobdir);

    // 
    // Process request parameters.
    // 
    if(isset($_REQUEST['data'])) {
	// 
	// Save the data to file.
	// 
	if(!file_put_contents($indata, $_REQUEST['data'])) {
	    cleanup_jobdir($jobdir, $indata);
	    if(strlen($_REQUEST['data']) == 0) {
		error_exit("No job data was submitted");
	    }
	    else {
		error_exit("Failed save data to file");
	    }
	}
    }
    else {
	// 
	// Make sure the uploaded file is posted file and not an
	// system file, i.e. /etc/passwd
	// 
	if(is_uploaded_file($_FILES['file']['tmp_name'])) {
	    if(!rename($_FILES['file']['tmp_name'], $indata)) {
		cleanup_jobdir($jobdir, $_FILES['file']['tmp_name']);
		error_exit("Failed move uploaded file");
	    }
	}
	else {
	    rmdir($jobdir);
	    if(isset($_FILES['file']['error'])) {
		switch($_FILES['file']['error']) {
		 case UPLOAD_ERR_INI_SIZE:
		    // 
		    // Value: 1; The uploaded file exceeds the upload_max_filesize directive in php.ini. 
		    //
		    error_exit("The uploaded file exceeds PHP's maximum allowed filesize");
		    break;
		 case UPLOAD_ERR_FORM_SIZE:
		    // 
		    // Value: 2; The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form. 
		    //
		    error_exit("The uploaded file exceeds forms maximum allowed filesize");
		    break;
		 case UPLOAD_ERR_PARTIAL:
		    //
		    // Value: 3; The uploaded file was only partially uploaded. 
		    //
		    error_exit("The uploaded file was only partially uploaded");
		    break;
		 case UPLOAD_ERR_NO_FILE:
		    //
		    // Value: 4; No file was uploaded. 
		    // 
		    error_exit("No file was uploaded");
		    break;
		 case UPLOAD_ERR_NO_TMP_DIR:
		    // 
		    // Value: 6; Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3. 
		    // 
		    error_exit(sprintf("Missing a temporary folder, contact %s", CONTACT_STRING));
		    break;
		 case UPLOAD_ERR_CANT_WRITE:
		    // 
		    // Value: 7; Failed to write file to disk. Introduced in PHP 5.1.0. 
		    //
		    error_exit(sprintf("Failed to write file to disk, contact %s", CONTACT_STRING));
		    break;
		 case UPLOAD_ERR_EXTENSION:
		    // 
		    // Value: 8; File upload stopped by extension. Introduced in PHP 5.2.0.
		    //
		    error_exit("File upload stopped by extension");
		    break;
		}
	    }
	    else {
		error_exit("No uploaded file");
	    }
	}
    }

    // 
    // The filesize test on uploaded data applies to both HTTP uploaded file
    // and data saved from request parameter data. Both gets saved to file
    // pointed to by $indata.
    // 
    if(filesize($indata) < UPLOAD_MIN_FILESIZE) {
	cleanup_jobdir($jobdir, $indata);
	error_exit(sprintf("Uploaded file is too small (requires filesize >= %d bytes)", UPLOAD_MIN_FILESIZE));
    }
    
    // 
    // File uploaded or created. Now we just has to start the batch
    // job. The path to the wrapper script path must be absolute.
    // 
    $resdir = sprintf("%s/result", $jobdir);
    if(!create_directory($resdir, CACHE_PERMISSION, true)) {
	cleanup_jobdir($jobdir, $indata);
	error_exit("Failed create result directory");
    }
    $script = realpath(dirname(__FILE__) . "/../utils/script.sh");
    $command = sprintf("%s %s %s %s", $script, $jobdir, $indata, $resdir);
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
    // update to submit the same data or file twice or more.
    // 
    header("Location: index.php");    
}

// 
// Validate request parameters.
// 
if(isset($_REQUEST['sort'])) {
    check_request_param("sort", array( "none", "started", "jobid", "state" ));
} 
else {
    $_REQUEST['sort'] = "none";
}
if(isset($_REQUEST['filter'])) {
    check_request_param("filter", array( "all", "pending", "running", "finished", "error", "crashed" ));
}
else {
    $_REQUEST['filter'] = "all";
}
if(isset($_REQUEST['show'])) {
    check_request_param("show", array( "submit", "queue" ));
}

// 
// Show form and running and finished jobs.
// 
show_page();

?>
