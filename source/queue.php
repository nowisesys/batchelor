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
if(file_exists("../include/hooks.inc")) {
    include "../include/hooks.inc";
}

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
// This function presents the job queue in text only or
// graphic mode depending on config preferences.
// 
function show_jobs_table(&$jobs)
{
    print "<h2><img src=\"icons/nuvola/services.png\"> Job Queue:</h2>\n";
    print "<table><tr><td align=\"left\"><span id=\"secthead\">Filter Options:</span></td>\n";
    print "<td><form action=\"queue.php\" method=\"get\">\n";
    print "<input type=\"hidden\" name=\"show\" value=\"queue\" />\n";
    print "<table><tr>\n";
    print_select("Sort on", "sort", array( "None" => "none", "Started" => "started", 
					   "Job ID" => "jobid", "Status" => "state" )); 
    print_select("Show", "filter",  array( "All" => "all", "Unfinished" => "waiting", "Pending" => "pending", 
					   "Running" => "running", "Finished" => "finished", 
					   "Warning" => "warning", "Error" => "error", "Crashed" => "crashed" ));
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
    // 
    // Add button for deleting currently shown jobs.
    // 
    printf("<br>\n");
    printf("<form action=\"delete.php\" method=\"GET\">\n");
    printf("  <input type=\"hidden\" name=\"filter\" value=\"%s\" />\n", $_REQUEST['filter']);
    printf("  <input type=\"submit\" name=\"multiple\" value=\"Delete Jobs\" title=\"Delete all jobs in this list\" />\n");
    printf("</form>\n");
}

// 
// Show job queue in graphic mode.
// 
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
	 case "warning":
	    $title = sprintf("started %s, \nfinished (with warnings) %s",
			     format_timestamp($job['started']),
			     format_timestamp($job['stderr']));
	    break;	    
	 case "error":
	    $title = sprintf("started %s, \nfinished (with errors) %s", 
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
	if($job['state'] == "finished" || $job['state'] == "warning") {
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

//
// Show job queue in text only mode.
// 
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
	print "<h2><img src=\"icons/nuvola/network.png\"> Submit data for processing:</h2>\n";

	// 
	// Put both forms inside an table to get them aligned. This looks much better
	// both in GUI and text based browsers.
	//
	print "<table>\n";
	
	// 
	// The form for uploading a file.
	// 
	print "<tr><td>Process file:</td><td>\n";
	print "<form enctype=\"multipart/form-data\" action=\"queue.php\" method=\"POST\">\n";
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
	print "   <input name=\"file\" type=\"file\" />\n";
	print "   <input type=\"submit\" value=\"Send File\" />\n";
	print "</form>\n";
	print "</td></tr>\n";
	
	// 
	// The form for submitting a data.
	// 
	print "<tr><td>Process data:</td><td>\n";	
	print "<form action=\"queue.php\" method=\"GET\">\n";
	print "   <textarea name=\"data\" cols=\"50\" rows=\"8\"></textarea>\n";
	print "   <input type=\"submit\" value=\"Send Data\" />\n";
	if(isset($_REQUEST['sort'])) {
	    printf("   <input type=\"hidden\" name=\"sort\" value=\"%s\" />\n", $_REQUEST['sort']);
	}
	if(isset($_REQUEST['filter'])) {
	    printf("   <input type=\"hidden\" name=\"filter\" value=\"%s\" />\n", $_REQUEST['filter']);
	}
	print "</form>\n";
	print "</td></tr></table>\n";
    }
    
    if($_REQUEST['show'] == "queue") {
	// 
	// Show jobs.
	//
	show_jobs_table($jobs);
    }
    
    // 
    // Should we show an error message?
    //
    if(isset($_REQUEST['error'])) {
	if(isset($_REQUEST['type'])) {
	    switch($_REQUEST['type']) {
	     case "zip":
		print_message_box("error", sprintf("Failed create zip archive. This is probably a permanent error.<br>Please report it to %s", CONTACT_STRING));
		break;
	     case "hostid":
		print_message_box("error", "Failed get host ID. Do you have cookies enabled?");
		break;
	     case "params":
		print_message_box("error", "One or more required request parameters is missing or unset");
		break;
	     case "resdir":
		print_message_box("error", "The result directory is missing");
		break;
	    }
	} 
	else {
	    print_message_box("error", "The last operation caused an error.<br>Please retry with new data and parameters.");
	}
    }
    if(isset($GLOBALS['error'])) {
	print_message_box("error", $GLOBALS['error']);
    }
    if(isset($_REQUEST['queued'])) {
	print_message_box("info", "The submitted job has been queued.<br>Click on <a href=\"queue.php?show=queue\">Show Queue</a> to view its status and download the result.");
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
	if(isset($_REQUEST['show'])) {
	    if($_REQUEST['show'] == "submit") {
		printf("%s - Submit Jobs", HTML_PAGE_TITLE);
	    }
	    else if($_REQUEST['show'] == "queue") {
		printf("%s - Queue Manager", HTML_PAGE_TITLE);
	    }
	}
	else {
	    printf("%s - Batch Job Queue Manager", HTML_PAGE_TITLE);
	}
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
    
    if(isset($_REQUEST['show']) && $_REQUEST['show'] == "queue") {
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
update_hostid_cookie();

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
    if(UPLOAD_PRESERVE_FILENAME && isset($_FILES['file']['name'])) {
	$indata = sprintf("%s/%s", $jobdir, $_FILES['file']['name']);
    }
    else {
	$indata = sprintf("%s/indata", $jobdir);
    }
    
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
	    if(UPLOAD_PRESERVE_FILENAME) {
		if(!symlink($indata, sprintf("%s/indata", $jobdir))) {
		    error_exit("Failed symlink uploaded file");
		}
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
    // Call pre enqueue hook if function is defined.
    // 
    if(function_exists("pre_enqueue_hook")) {
	$error = "";
	if(!pre_enqueue_hook($indata, $error)) {
	    cleanup_jobdir($jobdir, $indata);
	    error_exit($error);
	}
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
    // Call post enqueue hook if function is defined.
    // 
    if(function_exists("post_enqueue_hook")) {
	post_enqueue_hook($indata, $jobdir);
    }
    
    // 
    // Redirect the browser to an empty queue.php to prevent page
    // update to submit the same data or file twice or more.
    // 
    header("Location: queue.php?queued=yes");
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
    check_request_param("filter", array( "all", "waiting", "pending", "running", "finished", "warning", "error", "crashed" ));
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
