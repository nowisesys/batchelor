<?php

// -------------------------------------------------------------------------------
//  Copyright (C) 2008 Anders Lövgren
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
// Common user interface functions.
// 

// 
// Output additional HTTP headers.
// 
function print_header()
{
    printf("<!-- A web application using: batchelor - batch job queue manager -->\n");
    if(isset($GLOBALS['refresh'])) {
	printf("<meta http-equiv=\"refresh\" content=\"%d\" />", PAGE_REFRESH_RATE);
    }
}

// 
// Print menu accoring to user preferences.
// 
function print_menu()
{
    $queue = array( "Submit Jobs" => array( "href" => "queue.php?show=submit", "attr" => "", "icon" => "network.png" ),
		    "Show Queue"  => array( "href" => "queue.php?show=queue",  "attr" => "", "icon" => "services.png" ));
    $utils = array( "Statistics" => array( "href" => "statistics.php", "attr" => "", "icon" => "statistics.png" ),
		    "About" => array( "href" => "about.php", "attr" => "target=\"_blank\"", "icon" => "info.png" ),
		    "Help"  => array( "href" => "help.php",  "attr" => "target=\"_blank\"", "icon" => "help.png" ));
    
    if(!defined("MENU_ORIENTATION")) {
	define ("MENU_ORIENTATION", "vertical");
    }
    if(!defined("MENU_STYLE")) {
	define ("MENU_STYLE", "graphic");
    }
    
    if(MENU_ORIENTATION == "vertical") {
	print_menu_vertical($queue, $utils);
    } 
    else {
	print_menu_horizontal($queue, $utils);
    }
}

// 
// Print vertical styles menu, suitable for a left-side menu.
// 
function print_menu_vertical($queue, $utils)
{
    if(MENU_STYLE == "graphic") {
	echo "<div class=\"menuhead\"><img src=\"icons/nuvola/users.png\">&nbsp;&nbsp;Job Queue</div>\n";
    }
    echo "<ul class=\"menu\">\n";
    foreach($queue as $name => $link) {
	printf("<li><a href=\"%s\" %s>%s</a></li>\n", $link['href'], $link['attr'], $name);
    }
    if(MENU_STYLE != "compact") {
	echo "</ul>\n";
    }

    if(MENU_STYLE == "graphic") {
	echo "<div class=\"menuhead\"><img src=\"icons/nuvola/utils.png\">&nbsp;&nbsp;Utilities</div>\n";
    }
    if(MENU_STYLE != "compact") {	
	echo "<ul class=\"menu\">\n";
    }
    foreach($utils as $name => $link) {
	if($name == "Statistics" && STATISTICS_EXPOSED == "hidden") {
	    continue;
	}
	printf("<li><a href=\"%s\" %s>%s</a></li>\n", $link['href'], $link['attr'], $name);
    }
    echo "</ul>\n";
}

// 
// Print horizontal menu, suitable for a top-menu.
//
function print_menu_horizontal($queue, $utils)
{
    echo "<table><tr>\n";
    foreach($queue as $name => $opts) {
	echo "<td class=\"topmenuitem\">\n";
	if(MENU_STYLE == "graphic") {
	    printf("<img src=\"icons/nuvola/%s\">", $opts['icon']);
	}
	printf("<a href=\"%s\" %s>%s</a>", $opts['href'], $opts['attr'], $name);
	echo "</td>\n";
    }
    if(MENU_STYLE != "compact") {
	echo "<td width=\"60\">\n";
    }
    foreach($utils as $name => $opts) {
	echo "<td class=\"topmenuitem\">\n";
	if(MENU_STYLE == "graphic") {
	    printf("<img src=\"icons/nuvola/%s\">", $opts['icon']);
	}
	printf("<a href=\"%s\" %s>%s</a>", $opts['href'], $opts['attr'], $name);
	echo "</td>\n";
    }
    echo "</tr></table>\n";
}

// 
// Print page footer.
// 
function print_footer()
{
    print  "<table>\n";
    if(FOOTER_SHOW_NOTES) {
	print  "  <tr>\n";
	printf("    <td class=\"footer\">Last updated: %s<br>Contact: %s</td><td width=\"100%%\">&nbsp;</td>\n", format_timestamp(time()), CONTACT_STRING);
	print  "    <td class=\"notes\">This web application is powered by <a href=\"about.php?sect=batchelor\" target=\"_blank\">Batchelor</a></td></tr>\n";
	print  "  <tr><td>&nbsp;</td></tr>\n";
    }
    if(FOOTER_SHOW_COPYING) {
	print  "  <tr>\n";
	print  "    <td class=\"copyright\" colspan=\"3\">Batchelor is Copyright &copy; 2007-2008 by Anders L&ouml;vgren and the <a href=\"http://www.bmc.uu.se\" target=\"blank\">Computing Department at Uppsala Biomedical Centre</a>, Uppsala University</td></tr>\n";
    }
    print  "</table>\n";
}

// 
// Print context help.
// 
function print_context_help()
{
    switch(basename($_SERVER['PHP_SELF'])) {
     case "queue.php":
	if(isset($_REQUEST['show']) && $_REQUEST['show'] == "queue") {
	    $what = "queue";
	} else {
	    $what = "submit";
	}
	break;
     case "statistics.php":
	$what = "stats";
	break;
    }
    
    if(function_exists("context_help_hook")) {
	context_help_hook($what);
    } else {
	context_help_system($what);
    }
}

// 
// The function that actually prints context help.
// 
function context_help_system($what)
{
    if(HELP_LEVEL == 0) {
	return;
    }
    
    switch($what) {
     case "submit":
	if(HELP_LEVEL > 2) {
	    print "<p>Browse and upload a file or copy and paste the data into the 'process data field'.</p>\n";
	}
	if(HELP_LEVEL > 0) {
	    if(UPLOAD_MIN_FILESIZE != 0 && UPLOAD_MAX_FILESIZE != 0) {
		printf("<p>Uploaded data must be <b>between %s and %s.</b></p>\n", 
		       bytes_to_string(UPLOAD_MIN_FILESIZE), 
		       bytes_to_string(UPLOAD_MAX_FILESIZE));
	    } else if(UPLOAD_MIN_FILESIZE != 0) {
		printf("<p>Uploaded data must be <b>at least %s.</b></p>\n", 
		       bytes_to_string(UPLOAD_MIN_FILESIZE));
	    } else if(UPLOAD_MAX_FILESIZE != 0) {
		printf("<p><b>Maximum accepted size</b> of uploaded data is %s.</p>\n", 
		       bytes_to_string(UPLOAD_MAX_FILESIZE));
	    } else {
		printf("<p>Theres no min/max size defined for uploaded data except for PHP limit on %s</p>\n",
		       ini_get("upload_max_filesize"));
	    }
	}
	if(HELP_LEVEL > 1) {
	    print "<p>The uploaded job gets queued for execution and can be monitored in the <a href=\"queue.php?show=queue\">job queue</a> viewer.</p>\n";
	}
	break;
     case "queue":
	if(HELP_LEVEL > 1) {
	    print "<p>Use the <b>'sort on'</b> and <b>'show'</b> droplist to filter jobs in queue.</p>\n"; 
	}
	if(HELP_LEVEL > 0) {
	    print "<p>Pressing the <b>'delete jobs'</b> button will delete all jobs that are currently shown.</p>\n";
	}
	if(HELP_LEVEL > 2) {
	    print "<p>Only result for jobs finished with <b>success</b> or <b>warning</b> status can be downloaded.</p>\n";
	    print "<p>Delete individual jobs by pressing its <b>'thrash bin'</b> icon.</p>\n";
	}
	if(HELP_LEVEL > 0) {
	    if(defined("ENABLE_JOB_CONTROL")) {
		if(ENABLE_JOB_CONTROL == "simple") {
		    printf("<p>Simplified job control is enabled. Clicking the 'trash bin' icon on a running job will send the <b>'%s' signal</b> to the job.</p>\n", JOB_CONTROL_ACTION);
		} else if(ENABLE_JOB_CONTROL == "advanced") {
		    print "<p>Advanced job control is enabled. Running jobs can be <b>controlled</b> by sending <b>signals</b>. Stopped jobs can only be <b>resumed</b> (continue) or <b>killed</b>.</p>\n";
		}
	    }
	}
	if(HELP_LEVEL > 2) {
	    print "<p>Click on the job ID link to view <b>details</b> and <b>uploaded data</b> for that specific job.</p>\n";
	}
	if(HELP_ICON_INFO) {
	    if(HELP_LEVEL > 0) {
		print "<p><u>Description of exit status icons:</u>\n";
		print "<ul>\n";
		print "  <li><img src=\"icons/nuvola/finished.png\" alt=\"finished icon\"> = Finished successful</li>";
		print "  <li><img src=\"icons/nuvola/warning.png\"  alt=\"warning icon\"> = Finished with warnings</li>";
		print "  <li><img src=\"icons/nuvola/error.png\"    alt=\"error icon\"> = Finished with errors</li>";
		print "  <li><img src=\"icons/nuvola/crashed.png\" alt=\"crashed icon\"> = Job terminated abnormal</li>";
		print "</ul></p>\n";
	    }
	    if(HELP_LEVEL > 1) {
		print "<p><u>Description of queue status icons:</u>\n";
		print "<ul>\n";
		print "  <li><img src=\"icons/nuvola/pending.png\" alt=\"pending icon\"> = Job is queued but not running</li>";
		print "  <li><img src=\"icons/nuvola/running.png\"  alt=\"running icon\"> = Job has been started</li>";
		print "</ul></p>\n";
	    }
	}
	break;
     case "stats":
	if(HELP_LEVEL > 0) {
	    print "<p>The <b>system</b> statistics is collected from all users of this system.</p>\n";
	    print "<p>The <b>personal</b> statistics is individual for each each user and/or host.</p>\n";
	}
	if(HELP_LEVEL > 2) {
	    print "<p>The statistics is organized as an hierarchic, browsable tree structure.</p>\n";
	}
	if(HELP_LEVEL > 1) {
	    switch(STATISTICS_EXPOSED) {
	     case "public":
		print "<p>The statistics is <b>public</b> and accessable/browsable by anyone.</p>\n";
		break;
	     case "hidden":
		print "<p>The statistics is <b>hidden</b> meaning that the link is not exposed but statistics is still accessable.</p>\n";
		break;
	     case "locked":
		printf("<p>The statstics is <b>locked</b> and only <b>trusted hosts</b> can access it. These hosts counts as trusted: <b>%s</b></p>\n",
		       implode(', ', explode(':', STATISTICS_TRUSTED)));	    
		break;
	    }
	}
    }
}

function print_common_html($what)
{
    switch($what) {
     case "header":
	print_header();
	break;
     case "menu":
	print_menu();
	break;
     case "footer":
	print_footer();
	break;
     case "help":
	if(HELP_STYLE == "graphic") {
	    echo "<div class=\"menuhead\"><img src=\"icons/nuvola/help.png\">&nbsp;&nbsp;Help</div>\n";
	}
	print_context_help();
	break;
    }
}

function load_ui_template($type)
{
    if(file_exists("../template/$type.ui")) {
	include "../template/$type.ui";
    } else if(file_exists("../template/$type.ui.in")) {
	include "../template/$type.ui.in";
    }
}
