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
// Show details about a job.
// 
//
// Get configuration.
// 
include "../conf/config.inc";
include "../include/common.inc";
include "../include/ui.inc";

if (file_exists("../include/hooks.inc")) {
        include("../include/hooks.inc");
}

// 
// The error handler.
// 
function error_handler($type)
{
        // 
        // Redirect caller back to queue.php and let it report an error.
        // 
        header("Location: queue.php?show=queue&error=details&type=$type");
}

function print_title()
{
        if (isset($GLOBALS['indata'])) {
                printf("%s - Data for Job ID %s", HTML_PAGE_TITLE, $GLOBALS['jobid']);
        } else {
                printf("%s - Details for Job ID %s", HTML_PAGE_TITLE, $GLOBALS['jobid']);
        }
}

function print_body()
{
        if (isset($GLOBALS['indata'])) {
                printf("<h2><img src=\"icons/nuvola/info.png\"> Data for Job ID %s</h2>\n", $GLOBALS['jobid']);
                $indata = sprintf("%s/indata", $GLOBALS['jobdir']);

                if (function_exists("show_indata_hook")) {
                        show_indata_hook($indata);
                } else {
                        printf("<span id=\"secthead\">Uploaded data:</span>\n");

                        // 
                        // Single line input data must be wrapped by browser, or
                        // the output may look like empty space on the page.
                        // 
                        $content = file(sprintf("%s/indata", $GLOBALS['jobdir']));
                        if (count($content) == 1) {
                                printf("<p>%s</p>\n", $content[0]);
                        } else {
                                printf("<p><pre>%s</pre></p>\n", implode("\n", $content));
                        }
                }
        } elseif (isset($GLOBALS['warning'])) {
                printf("<h2><img src=\"icons/nuvola/warning.png\"> Warning messages for Job ID %s</h2>\n", $GLOBALS['jobid']);
                print "<p>\n";
                readfile(sprintf("%s/warning", $GLOBALS['jobdir']));
                print "<p>\n";
        } else {
                printf("<h2><img src=\"icons/nuvola/info.png\"> Details for Job ID %s</h2>\n", $GLOBALS['jobid']);
                $cwd = getcwd();
                chdir($GLOBALS['jobdir']);

                $started = null;
                $finished = null;

                $stdout = null;
                $stderr = null;

                if (file_exists("started")) {
                        $started = trim(file_get_contents("started"));
                }
                if (file_exists("finished")) {
                        $finished = trim(file_get_contents("finished"));
                }

                if (file_exists("stdout") && filesize("stdout") > 0) {
                        $stdout = file_get_contents("stdout");
                }
                if (file_exists("stderr") && filesize("stderr") > 0) {
                        $stderr = file_get_contents("stderr");
                }

                // 
                // Display job details.
                // 

                if (function_exists("show_result_hook")) {
                        show_result_hook($started, $finished, $stdout, $stderr);
                } else {
                        $proctime = 0;
                        if (isset($started) && isset($finished)) {
                                $proctime = $finished - $started;
                        }
                        printf("<span id=\"secthead\">Process time:</span>\n");
                        printf("<p>\n");
                        printf("<b>Started:</b> %s&nbsp;&nbsp;&nbsp;&nbsp;", format_timestamp($started));
                        printf("<b>Finished:</b> %s&nbsp;&nbsp;&nbsp;&nbsp;", $finished != 0 ? format_timestamp($finished) : "&nbsp;---&nbsp;");
                        if ($proctime) {
                                printf("<b>Total job time:</b> %s\n", seconds_to_string($proctime));
                        } else {
                                if ($finished) {
                                        print "<b>Total job time:</b> < 1 sec\n";
                                } else {
                                        print "<b>Total job time:</b> &nbsp;---&nbsp;\n";
                                }
                        }
                        printf("</p><br>\n");

                        if (isset($stdout)) {
                                printf("<span id=\"secthead\">Output from job:</span>\n");
                                printf("<p>%s</p>\n", preg_replace(
                                                array(
                                                '/\n/',
                                                '/(using|options|defaults|created|exiting)/i'
                                                ), array(
                                                '<br>',
                                                '<b>$1</b>'
                                                ), $stdout));
                        }
                        if (isset($stderr)) {
                                printf("<span id=\"secthead\">Error log:</span>\n");
                                printf("<p>%s</p>\n", $stderr);
                        }
                }
                printf("<span id=\"secthead\">More information:</span>\n");
                printf("<p><ul>\n");
                printf("<li><a href=\"details.php?jobid=%s&result=%s&data=1\" target=\"_blank\">View indata</a></li>", $_REQUEST['jobid'], $_REQUEST['result']);
                printf("<li><a href=\"download.php?jobid=%s&result=%s&what=indata\">Download indata</a></li>", $_REQUEST['jobid'], $_REQUEST['result']);
                printf("<li><a href=\"download.php?jobid=%s&result=%s&what=result\">Download result</a></li>", $_REQUEST['jobid'], $_REQUEST['result']);
                printf("</ul></p>\n");

                chdir($cwd);
        }
}

function print_html($what)
{
        switch ($what) {
                case "body":
                        print_body();
                        break;
                case "title":
                        print_title();
                        break;
                default:
                        print_common_html($what);    // Use default callback.
                        break;
        }
}

// 
// Check required parameters.
// 
if (!isset($_REQUEST['jobid']) || !isset($_REQUEST['result'])) {
        error_handler("params");
}
if (!isset($_COOKIE['hostid'])) {
        error_handler("hostid");
}

// 
// Get request parameters.
// 
$jobid = $_REQUEST['jobid'];            // Job ID.
$jobdir = $_REQUEST['result'];          // Job directory.
if (isset($_REQUEST['data'])) {
        $indata = $_REQUEST['data'];    // Show indata.
}
if (isset($_REQUEST['warn'])) {
        $warning = $_REQUEST['warn'];   // Show warning
}

// 
// Get hostid from cookie.
// 
$hostid = $_COOKIE['hostid'];

// 
// Build absolute path to job directory:
// 
$jobdir = sprintf("%s/jobs/%s/%s", CACHE_DIRECTORY, $hostid, $jobdir);

// 
// If job directory is missing, the show an error message.
// 
if (!file_exists($jobdir)) {
        die("The job directory is missing");
}

load_ui_template("popup");

