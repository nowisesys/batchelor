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

if(!defined("QUEUE_FORMAT_COMPACT")) {
    define ("QUEUE_FORMAT_COMPACT", false);
}
if(!defined("QUEUE_SHOW_NAMES")) {
    define ("QUEUE_SHOW_NAMES", true);
}
if(!defined("QUEUE_SORT_ORDER")) {
    define ("QUEUE_SORT_ORDER", "desc");
}
$GLOBALS['order'] = QUEUE_SORT_ORDER;

if(!defined("UPLOAD_TEXTAREA_WRAPPING")) {
    define ("UPLOAD_TEXTAREA_WRAPPING", "off");
}
if(!defined("UPLOAD_MIN_FILESIZE")) {
    define ("UPLOAD_MIN_FILESIZE", 0);
}
if(!defined("UPLOAD_MAX_FILESIZE")) {
    define ("UPLOAD_MAX_FILESIZE", 0);
}
if(!defined("UPLOAD_SUBJOB_LIMIT")) {
    define ("UPLOAD_SUBJOB_LIMIT", 5);
}
if(!defined("FORM_SUBMIT_TYPE")) {
    define ("FORM_SUBMIT_TYPE", "file");   // send file by default.
}
if(!defined("FORM_PARAMS_LOCATION")) {
    define ("FORM_PARAMS_LOCATION", "compact");
}

include "../include/common.inc";
include "../include/queue.inc";
include "../include/ui.inc";
if(file_exists("../include/hooks.inc")) {
    include("../include/hooks.inc");
}

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
    $sort   = array( "None"    => "none", 
		     "Started" => "started", 
		     "Job ID"  => "jobid", 
		     "Status"  => "state" );
    
    $filter = array( "All"      => "all", 
		     "Unfinished" => "waiting", 
		     "Pending"  => "pending", 
		     "Running"  => "running", 
		     "Finished" => "finished", 
		     "Warning"  => "warning", 
		     "Error"    => "error", 
		     "Crashed"  => "crashed" );

    // 
    // Provide sort on names too.
    // 
    if(QUEUE_SHOW_NAMES) {
	$sort["Name"] = "name";
    }
    
    print "<h2><img src=\"icons/nuvola/services.png\"> Job Queue:</h2>\n";
    print "<table><tr><td align=\"left\"><span id=\"secthead\">Filter Options:</span></td>\n";
    print "<td><form action=\"queue.php\" method=\"get\">\n";
    print "<input type=\"hidden\" name=\"show\" value=\"queue\" />\n";
    print "<table><tr>\n";
    print_select("Sort on", "sort", $sort);
    print_select("Show", "filter",  $filter);
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
    printf("  <input type=\"hidden\" name=\"sort\" value=\"%s\">\n", $_REQUEST['sort']);
    printf("  <input type=\"submit\" name=\"multiple\" value=\"Delete Jobs\" title=\"Delete all jobs showed in this list\" />\n");
    printf("</form>\n");
}

// 
// Show job queue in graphic mode.
// 
function show_jobs_table_icons(&$jobs)
{
    if(QUEUE_FORMAT_COMPACT) {
	$headers = array("Q", "F", "Started", "Job", "D", "X", "N");
	if(ENABLE_JOB_CONTROL == "advanced") {
	    array_push($headers, "Job Ctrl");
	}
    } else {
	$headers = array("Queued", "Finished", "Started", "Job", "Notice", "Download", "Delete");
	if(ENABLE_JOB_CONTROL == "advanced") {
	    array_push($headers, "Job Control");
	}
    }
    print "<div class=\"indent\"><table width=\"50%\"><tr>";
    foreach($headers as $header) {
	printf("<th>%s</th>", $header);
    }
    print "<tr>\n";
    
    foreach($jobs as $jobdir => $job) {	    
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
			     format_timestamp(isset($job['started']) ? $job['started'] : 0));
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
	    printf("<td nowrap>%s</td>", format_timestamp(isset($job['started']) ? $job['started'] : 0));
	}
	
	// 
	// Job column
	// 
	printf("<td nowrap><a href=\"details.php?jobid=%s&result=%s\" target=\"_blank\" title=\"%s\">Job %s</a></td>", 
	       $job['jobid'], $jobdir, $title, $job['jobid']);
		
	// 
	// Download, delete and warning (notice) column
	// 
	if(isset($job['warning'])) {
	    printf("<td nowrap><a href=\"details.php?jobid=%s&result=%s&warn=1\" title=\"this job was queued with warnings, click on the icon to show the warning message.\" target=\"_blank\"><img src=\"icons/nuvola/warning.png\" alt=\"warning\"></a></td>", $job['jobid'], $jobdir);
	} else {
	    printf("<td>&nbsp;</td>\n");
	}
	if($job['state'] == "finished" || $job['state'] == "warning") {
	    printf("<td><a href=\"download.php?jobid=%s&result=%s\" title=\"download result\"><img src=\"icons/nuvola/download.png\" alt=\"download\"></a></td>", $job['jobid'], $jobdir);
	}
	else {
	    printf("<td>&nbsp;</td>\n");
	}
	if(SHOW_JOB_DELETE_LINK && $job['state'] != "running") {
	    printf("<td nowrap><a href=\"delete.php?jobid=%s&result=%s&sort=%s&filter=%s\" title=\"delete job\"><img src=\"icons/nuvola/delete.png\" alt=\"delete\"></a></td></tr>", 
		   $job['jobid'], $jobdir, $_REQUEST['sort'], $_REQUEST['filter']);
	}
	if(ENABLE_JOB_CONTROL != "off" && $job['state'] == "running") {
	    if(ENABLE_JOB_CONTROL == "simple") {
		printf("<td nowrap><a href=\"jobcontrol.php?jobid=%s&result=%s&sort=%s&filter=%s&signal=%s\" title=\"send job the %s signal\"><img src=\"icons/nuvola/delete.png\" alt=\"signal\"></a></td></tr>", 
		       $job['jobid'], $jobdir, $_REQUEST['sort'], $_REQUEST['filter'], JOB_CONTROL_ACTION, JOB_CONTROL_ACTION);
	    } else {
		global $signals;

		$sigfile = sprintf("%s/jobs/%s/%s/signal", CACHE_DIRECTORY, $_COOKIE['hostid'], $jobdir);
		if(file_exists($sigfile)) {
		    $signal = file_get_contents($sigfile);
		}
		
		printf("<td nowrap>&nbsp;</td><td><form action=\"jobcontrol.php\" method=\"GET\">\n");
		printf("  <input type=\"hidden\" name=\"jobid\" value=\"%s\">\n", $job['jobid']);
		printf("  <input type=\"hidden\" name=\"result\" value=\"%s\">\n", $jobdir);
		printf("  <input type=\"hidden\" name=\"sort\" value=\"%s\">\n", $_REQUEST['sort']);
		printf("  <input type=\"hidden\" name=\"filter\" value=\"%s\">\n", $_REQUEST['filter']);
		
		printf("  <select name=\"signal\">\n");
		foreach($signals as $name => $arr) {
		    // 
		    // Stopped processes can only be resumed (cont) or killed (kill).
		    // Don't show continue for non-stopped processes.
		    // 
		    if(isset($signal)) {
			if($signal == "stop") {
			    if($name != "cont" && $name != "kill") {
				continue;
			    }
			}
			if($name == "stop" && $signal == "stop") {
			    continue;
			}
			if($name == "cont" && $signal != "stop") {
			    continue;
			}
		    } else if($name == "cont") {
			    continue;
		    }
		    if(JOB_CONTROL_ACTION == $name) {
			printf("  <option value=\"%s\" selected=\"selected\">%s</option>\n", $name, $arr['desc']);
		    } else {
			printf("  <option value=\"%s\">%s</option>\n", $name, $arr['desc']);
		    }
		}
		printf("  </select>\n");
		printf("  <input type=\"submit\" value=\"Send\">\n");
		printf("</form></td>\n");
	    }
	}
	// 
	// Job name (below and indented)
	// 
	if(isset($job['name'])) {
	    if(QUEUE_SHOW_NAMES) {
		$span = ENABLE_JOB_CONTROL == "advanced" ? 5 : 4;
		printf("<tr><td colspan=\"2\">&nbsp;</td><td colspan=\"%d\" class=\"name\">%s</td></tr>\n", $span, $job['name']);
	    }
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
	printf("<td nowrap><a href=\"details.php?jobid=%s&result=%s\" target=\"_blank\" title=\"%s\">Job %d</a></td>", 
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
	// 
	$links = array();
	if(SHOW_JOB_DELETE_LINK && $job['state'] != "running") {
	    array_push($links, sprintf("<a href=\"delete.php?jobid=%s&result=%s&sort=%s&filter=%s\">delete</a>", 
				       $job['jobid'], $jobdir, $_REQUEST['sort'], $_REQUEST['filter']));
	}
	if($job['state'] == "finished") {
	    array_push($links, sprintf("<a href=\"download.php?jobid=%s&result=%s\">download</a>", $job['jobid'], $jobdir));
	}
	printf("<td nowrap>%s</td></tr>\n", implode(", ", $links));
    }
    print "</table></div>\n";
}

// 
// Print form for submitting a file.
// 
function print_submit_file()
{    
    // 
    // The form for uploading a file.
    // 
    print "<form enctype=\"multipart/form-data\" action=\"queue.php\" method=\"POST\">\n";
    if(function_exists("params_form_hook") && FORM_PARAMS_LOCATION == "north") {
	print_form_hook();
    }
    print "<tr><td>Process file:</td><td>\n";
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
    if(isset($_REQUEST['proc'])) {
	printf("   <input type=\"hidden\" name=\"proc\" value=\"%s\" />\n", $_REQUEST['proc']);
    }
    print "   <!-- Name of input element determines name in \$_FILES array -->\n";
    print "   <input name=\"file\" type=\"file\" class=\"file\" size=\"50\" />\n";
    print "</td>\n";
    if(function_exists("params_form_hook") && FORM_PARAMS_LOCATION == "east") {
	print_form_hook();
    }
    print "</tr>\n";
    if(function_exists("params_form_hook") && 
       (FORM_PARAMS_LOCATION == "south" || FORM_PARAMS_LOCATION == "compact")) {
	print_form_hook();
    }
    print "<tr><td>&nbsp;</td><td><input type=\"submit\" value=\"Send File\" /></td></tr>\n";
    print "</form>\n";
}

// 
// Print form for submitting data.
// 
function print_submit_data()
{
    // 
    // The form for submitting data.
    // 
    print "<form action=\"queue.php\" method=\"POST\">\n";
    if(function_exists("params_form_hook") && FORM_PARAMS_LOCATION == "north") {
	print_form_hook();
    }
    print "<tr><td>Process data:</td><td>\n";	
    printf("   <textarea name=\"data\" cols=\"50\" rows=\"8\" wrap=\"%s\"></textarea>\n", UPLOAD_TEXTAREA_WRAPPING);
    if(isset($_REQUEST['sort'])) {
	printf("   <input type=\"hidden\" name=\"sort\" value=\"%s\" />\n", $_REQUEST['sort']);
    }
    if(isset($_REQUEST['filter'])) {
	printf("   <input type=\"hidden\" name=\"filter\" value=\"%s\" />\n", $_REQUEST['filter']);
    }
    if(isset($_REQUEST['proc'])) {
	printf("   <input type=\"hidden\" name=\"proc\" value=\"%s\" />\n", $_REQUEST['proc']);
    }
    print "</td>\n";
    if(function_exists("params_form_hook") && FORM_PARAMS_LOCATION == "east") {
	print_form_hook();
    }
    print "</tr>\n";
    if(function_exists("params_form_hook") && 
       (FORM_PARAMS_LOCATION == "south" || FORM_PARAMS_LOCATION == "compact")) {
	print_form_hook();
    }
    print "<tr><td>&nbsp;</td><td><input type=\"submit\" value=\"Send Data\" /></td></tr>\n";
    print "</form>\n";
}

// 
// An helper function for printing the params_form_hook().
// 
function print_form_hook()
{
    $addrow  = FORM_PARAMS_LOCATION == "east" ? false : true;
    $compact = FORM_PARAMS_LOCATION == "compact" ? true : false;
    
    if($addrow) {
	if($compact) {
	    print "<tr><td>&nbsp;</td><td>\n";
	    params_form_hook();
	    print "</td></tr>\n";
	} else {
	    print "<tr><td colspan=\"2\">\n";
	    params_form_hook();
	    print "</td></tr>\n";
	}
    } else {
	print "<td>\n";
	params_form_hook();
	print "</td>\n";
    }
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
	$submit = FORM_SUBMIT_TYPE;   // Current submitted type
	
	// 
	// Determine what form to show:
	// 
	if(isset($_REQUEST['proc']) && $_REQUEST['proc'] == "file") {
	    $submit = "file";
	} elseif(isset($_REQUEST['proc']) && $_REQUEST['proc'] == "data") {
	    $submit = "data";
	}
	
	print "<h2><img src=\"icons/nuvola/network.png\"> Submit data for processing:</h2>\n";

	// 
	// Print the form for either submitting file or data:
	// 
	print "<div class=\"submit\"><table>\n";
	print "<tr><td colspan=\"2\">\n"; 
	printf("<img src=\"icons/nuvola/%s.png\">\n", 
	       $submit == FORM_SUBMIT_TYPE ? "down" : "up");
	if($submit == "file") {
	    print "<a href=\"?show=submit&proc=data\" title=\"Switch to form for submitting data\">Toggle form</a>\n";
	} else {
	    print "<a href=\"?show=submit&proc=file\" title=\"Switch to form for submitting a file\">Toggle form</a>\n";
	}
	print "</td></tr><tr><td>&nbsp;</td></tr>\n";
	if($submit == "file") {
	    print_submit_file();
	} else {
	    print_submit_data();
	}
	print "</table><br></div>\n";
	print "<br>\n";
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
	     case "pid":
		if(isset($_REQUEST['reason'])) {
		    switch($_REQUEST['reason']) {
		     case "type":
			print_message_box("error", "Missing or invalid signal type for job control.");
			break;
		     case "file":
			print_message_box("error", "A matching process for the running job was not found.");
			break;
		     case "perm":
			print_message_box("error", "Failed control running job.");
			break;
		     case "proc":
			print_message_box("error", "The process was not found.");
			break;
		    }
		}
		break;
	     case "delete":
		print_message_box("error", "Failed delete the job directory.");
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
    elseif(isset($GLOBALS['warning'])) {
	print_message_box("warning", $GLOBALS['warning']);
    }
    elseif(isset($_REQUEST['queued'])) {
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
function show_page($error = null, $warning = null) 
{
    global $jobs;
    
    if(isset($error)) {
	$GLOBALS['error'] = $error;
    }
    if(isset($warning)) {
	$GLOBALS['warning'] = $warning;
    }
    
    if(isset($_REQUEST['show']) && $_REQUEST['show'] == "queue") {
	// 
	// Get array of all running and finished jobs for peer identified
	// by the hostid superglobal variable.
	// 	
	$jobs = get_jobs($GLOBALS['hostid'], $_REQUEST['sort'], $_REQUEST['filter']);
	if(!$jobs) {
	    log_errors();
	}
	
	if(PAGE_REFRESH_RATE > 0) {
	    // 
	    // Only use Ajax or output meta refresh tag if we got pending 
	    // or running jobs.
	    // 
	    foreach($jobs as $job) {
		if($job['state'] == "pending" || $job['state'] == "running") {
		    $GLOBALS['refresh'] = true;
		    if(isset($_REQUEST['js']) && $_REQUEST['js'] == "on") {
			$GLOBALS['ajax'] = true; // Enable Ajax
		    }
		    break;
		}
	    }
	}
    }
    
    load_ui_template("standard");
}

// 
// This function shows the form, including the form, and then
// terminates the script execution. A more polished alternative 
// to die().
//
function error_exit($str)
{
    show_page($str, null);
    exit(1);
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
    // We got job data submitted. Enqueue job, display error message
    // and exit if enqueuing fails. 
    // 
    $jobs = null;
    if(!enqueue_job(isset($_REQUEST['data']) ? $_REQUEST['data'] : null, $jobs)) {
	error_exit(get_last_error());
    }
    if(has_warnings()) {
	show_page(null, get_last_warning());
	exit(0);
    }
    
    // 
    // Redirect the browser to an empty queue.php to prevent page
    // update to submit the same data or file twice or more.
    // 
    header(sprintf("Location: queue.php?queued=yes&jobs=%d&proc=%s", 
		   count($jobs), isset($_REQUEST['proc']) ? $_REQUEST['proc'] : FORM_SUBMIT_TYPE));
}

// 
// Validate request parameters.
// 
if(isset($_REQUEST['sort'])) {
    check_request_param("sort", array( "none", "started", "jobid", "state", "name" ));
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
