<?php

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
    print "<hr><table width=\"50%\"><tr><th>Started</th><th>Job</th><th>Status</th><th>Links</th></tr>\n";
    foreach($jobs as $resdir => $job) {	    
	$label = sprintf("(%s)", $job['state']);
	switch($job['state']) {
	 case "pending":
	    $title = sprintf("started %s, \nwaiting in queue", 
			     format_timestamp($job['started'])); 
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
	printf("<tr align=\"center\"><td>%s</td>", format_timestamp($job['started']));
	
	// 
	// Job column
	// 
	printf("<td><a href=\"details.php?jobid=%d&result=%s\" target=\"_blank\" title=\"%s\">Job %d</a></td>", 
	       $job['jobid'], $resdir, $title, $job['jobid']);
	
	// 
	// Status column
	// 
	printf("<td>%s</td>", $label);
	
	// 
	// Links column
	$links = array();
	if(SHOW_JOB_DELETE_LINK && $job['state'] != "running") {
	    array_push($links, sprintf("<a href=\"delete.php?jobid=%d&result=%s\">delete</a>", $job['jobid'], $resdir));
	}
	if($job['state'] == "finished") {
	    array_push($links, sprintf("<a href=\"download.php?jobid=%d&result=%s\">download</a>", $job['jobid'], $resdir));
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

    print "<html><head><title>Submit data for processing</title></head>\n";
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
    $resdir = sprintf("%s/jobs/%s", CACHE_DIRECTORY, $GLOBALS["hostid"]);
    if(!file_exists($resdir)) {
	if(!mkdir($resdir, CACHE_PERMISSION, true)) {
	    error_exit("Failed create output directory");
	}
    }
    
    $resdir = sprintf("%s/%d", $resdir, time());
    if(!mkdir($resdir, CACHE_PERMISSION, true)) {
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
    $seqfile = sprintf("%s/sequence", $resdir);

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
		rmdir($resdir);
		error_exit(sprintf("Uploaded file is too small (requires filesize >= %d bytes)", MIN_FILE_SIZE));
	    }
	    if(!rename($_FILES['file']['tmp_name'], $seqfile)) {
		error_exit("Failed move uploaded sequence file");
	    }
	}
	else {
	    error_exit("No uploaded file");
	}
    }
    
    // 
    // Filen uppladdad eller skapad. Nu �r det bara att starta ett
    // batch jobb f�r processning. S�kv�gen till skriptet m�ste vara
    // absolut.
    // 
    $script = realpath(dirname(__FILE__) . "/../include/script.sh");
    $command = sprintf("%s %s %s", $script, $resdir, $seqfile);
    $job = run_process($command, $resdir);
    
    // 
    // Save jobid to file in result dir.
    // 
    if(!file_put_contents(sprintf("%s/jobid", $resdir), $job['jobid'])) {
	error_exit("Failed save jobid");
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
