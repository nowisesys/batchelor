<?php

// -------------------------------------------------------------------------------
//  Copyright (C) 2007-2012 Anders Lövgren
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
// from the command line or as a cron job (preferable with nice 20).
// 
// The statistics is collected in a directory structure organized like this:
// 
// cache/
//   +-- ...
//  ...
//   +-- stat/                                     // root of statistics
//        +-- cache.ser                            // serialiserad data (cache)
//        +-- hostid1/                             // per hostid statistics
//        |    +-- *.png                           // graphics (1)
//        |    +-- summary.dat                     // text data (statistics)
//        |    +-- 2008/                           // by year statistics
//        |         +-- *.png                      // graphics (2)
//        |         +-- summary.dat                // text data (statistics)
//        |         +-- 03/                        // by month statistics
//        |              +-- *.png                 // graphics (2)
//        |              +-- summary.dat           // text data (statistics)
//        |              +-- 12/                   // by day statistics
//        |                   +-- *.png            // graphics (2)
//        |                   +-- summary.dat      // text data (statistics)
//        |                   +-- 15/              // by hour statistics
//        |                        +-- summary.dat // text data (statistics)
//        +-- hostid2/                             // per hostid statistics
//        |     +-- ...
//        +-- hostid3/                             // per hostid statistics
//       ...
//        +-- all/                                 // global statistics
//             +-- *.png                           // graphics (3)
//             +-- summary.dat                     // text data (statistics)
//             +-- 2008/                           // by year statistics
//                  +-- *.png                      // graphics (4)
//                  +-- summary.dat                // text data (statistics)
//                  +-- 03/                        // by month statistics
//                       +-- *.png                 // graphics (4)
//                       +-- summary.dat           // text data (statistics)
//                       +-- 12/                   // by day statistics
//                            +-- *.png            // graphics (4)
//                            +-- summary.dat      // text data (statistics)
//                            +-- 15/              // by hour statistics
//                                 +-- summary.dat // text data (statistics)
// 
// (1): proctime.png, state.png, submit.png
// (2): proctime.png, submit.png
// (3): proctime.png, state.png, submit.png, sysload_hourly.png, sysload_total.png, sysload_weekly.png
// (4): proctime.png, submit.png, sysload.png
// 
//
// The script should only be run in CLI mode.
//
if (isset($_SERVER['SERVER_ADDR'])) {
        die("This script should be runned in CLI mode.\n");
}

include "../conf/config.inc";
include "../include/common.inc";
include "../include/queue.inc";
include "../include/getopt.inc";

if (USE_JPGRAPH_LIB) {
        if (file_exists("../conf/jpgraph.inc")) {
                include "../conf/jpgraph.inc";
        } elseif (file_exists("../conf/jpgraph.inc.in")) {
                include "../conf/jpgraph.inc.in";
        }
        if (defined("JPGRAPH_LIB_PATH")) {
                ini_set("include_path", ini_get("include_path") . ":" . JPGRAPH_LIB_PATH);
        }
        foreach (array("jpgraph.php", "jpgraph_bar.php", "jpgraph_line.php", "jpgraph_pie.php") as $jpgraph) {
                if (!@include($jpgraph)) {
                        die(sprintf("Failed include %s (see JPGRAPH_LIB_PATH inside conf/jpgraph.inc)\n", $jpgraph));
                }
        }
        if (!class_exists("Graph")) {
                die("Class Graph (from JpGraph) is undefined (check your JpGraph installation)\n");
        }
}

if (file_exists("../include/hooks.inc")) {
        include "../include/hooks.inc";
}

define("LIST_QUEUE_ONCE", 0);
define("LIST_QUEUE_PER_HOSTID", 1);
define("LIST_QUEUE_PER_JOBDIR", 2);

// 
// Repair mode:
// 
define("REPAIR_MODE_MISSING", 1);
define("REPAIR_MODE_REPLACE", 2);
define("REPAIR_MODE_DEFAULT", "missing");

define("REPAIR_CHECK_DEFAULT", true);

// 
// Check mode for summary.dat files.
// 
define("REPAIR_CHECK_STRICT", 1);       // Die on missing keys
define("REPAIR_CHECK_INSERT", 2);       // Add default values
define("REPAIR_CHECK_IGNORE", 3);       // Ignore

define("REPAIR_CHECK_DEFAULT", REPAIR_CHECK_STRICT);

// 
// Filter types:
// 
define("FILTER_TYPE_PARABLE", 1);
define("FILTER_TYPE_DISTRIB", 2);

// 
// Filter settings:
// 
define("HOURLY_FUZZY_FILTER_POINTS", 8);
define("DAILY_FUZZY_FILTER_POINTS", 12);
define("PARABLE_FILTER_DIST", 0.7);      // disturbance
// 
// Filters used:
// 
define("HOURLY_FILTER_TYPE", FILTER_TYPE_DISTRIB);
define("DAILY_FILTER_TYPE", FILTER_TYPE_PARABLE);

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
        print "    -f,--force:       Recollect already collected statistics.\n";
        print "    -q,--qmode=val:   Set queue list mode (0-2, default 0)\n";
        print "    -r,--repair=mode: Repair statistics (defaults to missing).\n";
        print "    -d,--debug:       Enable debug.\n";
        print "    -v,--verbose:     Be more verbose.\n";
        print "    -h,--help:        This help.\n";
        print "    -V,--version:     Show version info.\n";
        print "\n";
        print "Notes:\n";
        print "  1. The --qmode value let users trade accuracy against processing time\n";
        print "     by defining how often the list of enqueued jobs should be fetched.\n";
        print "     Possible values are: 0 == once, 1 == per hostid, 2 == per jobdir.\n";
        print "     Lower values gives better performance at the slight chance of getting\n";
        print "     finished jobs classified as pending/running (thus ignored).\n";
        print "\n";
        print "  2. The --repair option processes existing summary.dat, using them to\n";
        print "     recreate the cache.ser that is used for generating graphics or\n";
        print "     statistics cache files (summary.dat). The mode argument is either\n";
        print "     'missing' or 'replace' and defines how data from summary.dat files\n";
        print "     is merged with entries in cache.ser.\n";
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
        if ($required) {
                if (!isset($val)) {
                        die(sprintf("%s: option '%s' requires an argument\n", $prog, $key));
                }
        } else {
                if (isset($val)) {
                        die(sprintf("%s: option '%s' do not take an argument\n", $prog, $key));
                }
        }
}

// 
// Parse command line options.
// 
function parse_options(&$argc, $argv, &$options)
{
        // 
        // Get command line options.
        // 
        $args = array();
        get_opt($argv, $argc, $args);
        foreach ($args as $key => $val) {
                switch ($key) {
                        case "-f":
                        case "--force":
                                check_arg($key, $val, false, $options->prog);
                                $options->force = 1;
                                break;
                        case "-q":
                        case "--qmode":
                                check_arg($key, $val, true, $options->prog);
                                if ($val > 2 || $val < 0) {
                                        die(sprintf("%s: value for '%s' is out of range, see --help\n", $options->prog, $key));
                                }
                                $options->qmode = $val;
                                break;
                        case "-r":
                        case "--repair":
                                if (!isset($val)) {
                                        $val = REPAIR_MODE_DEFAULT;
                                }
                                check_arg($key, $val, true, $options->prog);
                                if ($val != "missing" && $val != "replace") {
                                        die(sprintf("%s: value for '%s' is out of range, see --help\n", $options->prog, $key));
                                }
                                $options->repair = $val == "missing" ? REPAIR_MODE_MISSING : REPAIR_MODE_REPLACE;
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
        if (file_exists($filename)) {
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
        if (file_exists(sprintf("%s/finished", $jobdir))) {
                $stderr = sprintf("%s/stderr", $jobdir);
                if (file_exists($stderr) && filesize($stderr) > 0) {
                        if (file_exists(sprintf("%s/fatal", $jobdir))) {
                                return "error";
                        } else {
                                return "warning";
                        }
                } else {
                        return "success";
                }
        }

        // 
        // The job is either pending, running or crashed:
        // 
        $jobid = collect_file_content(sprintf("%s/jobid", $jobdir), 0);
        if (!isset($jobqueue[$jobid])) {
                return "crashed";
        } else {
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
        if (!isset($data[$hostid]['submit']['count'])) {
                $data[$hostid]['submit']['count'] = 0;
        }
        $data[$hostid]['submit']['count']++;

        // 
        // Submit by year:
        // 
        if (!isset($data[$hostid][$year]['submit']['count'])) {
                $data[$hostid][$year]['submit']['count'] = 0;
        }
        $data[$hostid][$year]['submit']['count']++;

        // 
        // Submit by month:
        // 
        if (!isset($data[$hostid][$year][$month]['submit']['count'])) {
                $data[$hostid][$year][$month]['submit']['count'] = 0;
        }
        $data[$hostid][$year][$month]['submit']['count']++;

        // 
        // Submit by day:
        // 
        if (!isset($data[$hostid][$year][$month][$day]['submit']['count'])) {
                $data[$hostid][$year][$month][$day]['submit']['count'] = 0;
        }
        $data[$hostid][$year][$month][$day]['submit']['count']++;

        // 
        // Submit by hour:
        // 
        if (!isset($data[$hostid][$year][$month][$day][$hour]['submit']['count'])) {
                $data[$hostid][$year][$month][$day][$hour]['submit']['count'] = 0;
        }
        $data[$hostid][$year][$month][$day][$hour]['submit']['count']++;
}

// 
// Update state count.
// 
function collect_state_count($hostid, &$data, $state)
{
        if (!isset($data[$hostid]['state'][$state])) {
                $data[$hostid]['state'][$state] = 0;
        }
        $data[$hostid]['state'][$state]++;
}

// 
// Helper function for counting floating avarage (arithmetric mean value):
// fn(x) : R -> R x(n - 1) = 1/n * ((n - 1) * x(n) + x(n + 1)), n >= 1
// 
function floating_mean_value($count, $acc, $last)
{
        return $acc * (($count - 1) / $count) + $last / $count;
}

// 
// Update process accounting.
// 
function collect_process_accounting($hostid, &$data, $queued, $started, $finished, $year, $month, $day, $hour)
{
        $waiting = $started - $queued;
        $running = $finished - $started;
        $process = $waiting + $running;

        // 
        // Process accounting total:
        // 
        if (!isset($data[$hostid]['proctime'])) {
                $data[$hostid]['proctime']['waiting'] = 0;         // mean value of queued time
                $data[$hostid]['proctime']['running'] = 0;         // mean value of execution time
                $data[$hostid]['proctime']['count'] = 0;           // number of jobs
                $data[$hostid]['proctime']['minimum'] = $process;  // minimum time from submit to finished
                $data[$hostid]['proctime']['maximum'] = 0;         // maximum time from submit to finished
        }
        $data[$hostid]['proctime']['count']++;
        $data[$hostid]['proctime']['waiting'] = floating_mean_value($data[$hostid]['proctime']['count'], $data[$hostid]['proctime']['waiting'], $waiting);
        $data[$hostid]['proctime']['running'] = floating_mean_value($data[$hostid]['proctime']['count'], $data[$hostid]['proctime']['running'], $running);
        if ($process < $data[$hostid]['proctime']['minimum']) {
                $data[$hostid]['proctime']['minimum'] = $process;
        }
        if ($process > $data[$hostid]['proctime']['maximum']) {
                $data[$hostid]['proctime']['maximum'] = $process;
        }

        // 
        // Process accounting by year:
        // 
        if (!isset($data[$hostid][$year]['proctime'])) {
                $data[$hostid][$year]['proctime']['waiting'] = 0;         // mean value of queued time
                $data[$hostid][$year]['proctime']['running'] = 0;         // mean value of execution time
                $data[$hostid][$year]['proctime']['count'] = 0;           // number of jobs
                $data[$hostid][$year]['proctime']['minimum'] = $process;  // minimum time from submit to finished
                $data[$hostid][$year]['proctime']['maximum'] = 0;         // maximum time from submit to finished
        }
        $data[$hostid][$year]['proctime']['count']++;
        $data[$hostid][$year]['proctime']['waiting'] = floating_mean_value($data[$hostid][$year]['proctime']['count'], $data[$hostid][$year]['proctime']['waiting'], $waiting);
        $data[$hostid][$year]['proctime']['running'] = floating_mean_value($data[$hostid][$year]['proctime']['count'], $data[$hostid][$year]['proctime']['running'], $running);
        if ($process < $data[$hostid][$year]['proctime']['minimum']) {
                $data[$hostid][$year]['proctime']['minimum'] = $process;
        }
        if ($process > $data[$hostid][$year]['proctime']['maximum']) {
                $data[$hostid][$year]['proctime']['maximum'] = $process;
        }

        // 
        // Process accounting by month:
        // 
        if (!isset($data[$hostid][$year][$month]['proctime'])) {
                $data[$hostid][$year][$month]['proctime']['waiting'] = 0;          // mean value of queued time
                $data[$hostid][$year][$month]['proctime']['running'] = 0;          // mean value of execution time
                $data[$hostid][$year][$month]['proctime']['count'] = 0;            // number of jobs
                $data[$hostid][$year][$month]['proctime']['minimum'] = $process;   // minimum time from submit to finished
                $data[$hostid][$year][$month]['proctime']['maximum'] = 0;          // maximum time from submit to finished
        }
        $data[$hostid][$year][$month]['proctime']['count']++;
        $data[$hostid][$year][$month]['proctime']['waiting'] = floating_mean_value($data[$hostid][$year][$month]['proctime']['count'], $data[$hostid][$year][$month]['proctime']['waiting'], $waiting);
        $data[$hostid][$year][$month]['proctime']['running'] = floating_mean_value($data[$hostid][$year][$month]['proctime']['count'], $data[$hostid][$year][$month]['proctime']['running'], $running);
        if ($process < $data[$hostid][$year][$month]['proctime']['minimum']) {
                $data[$hostid][$year][$month]['proctime']['minimum'] = $process;
        }
        if ($process > $data[$hostid][$year][$month]['proctime']['maximum']) {
                $data[$hostid][$year][$month]['proctime']['maximum'] = $process;
        }

        // 
        // Process accounting by day:
        // 
        if (!isset($data[$hostid][$year][$month][$day]['proctime'])) {
                $data[$hostid][$year][$month][$day]['proctime']['waiting'] = 0;          // mean value of queued time
                $data[$hostid][$year][$month][$day]['proctime']['running'] = 0;          // mean value of execution time
                $data[$hostid][$year][$month][$day]['proctime']['count'] = 0;            // number of jobs
                $data[$hostid][$year][$month][$day]['proctime']['minimum'] = $process;   // minimum time from submit to finished
                $data[$hostid][$year][$month][$day]['proctime']['maximum'] = 0;          // maximum time from submit to finished
        }
        $data[$hostid][$year][$month][$day]['proctime']['count']++;
        $data[$hostid][$year][$month][$day]['proctime']['waiting'] = floating_mean_value($data[$hostid][$year][$month][$day]['proctime']['count'], $data[$hostid][$year][$month][$day]['proctime']['waiting'], $waiting);
        $data[$hostid][$year][$month][$day]['proctime']['running'] = floating_mean_value($data[$hostid][$year][$month][$day]['proctime']['count'], $data[$hostid][$year][$month][$day]['proctime']['running'], $running);
        if ($process < $data[$hostid][$year][$month][$day]['proctime']['minimum']) {
                $data[$hostid][$year][$month][$day]['proctime']['minimum'] = $process;
        }
        if ($process > $data[$hostid][$year][$month][$day]['proctime']['maximum']) {
                $data[$hostid][$year][$month][$day]['proctime']['maximum'] = $process;
        }

        // 
        // Process accounting by hour:
        // 
        if (!isset($data[$hostid][$year][$month][$day][$hour]['proctime'])) {
                $data[$hostid][$year][$month][$day][$hour]['proctime']['waiting'] = 0;          // mean value of queued time
                $data[$hostid][$year][$month][$day][$hour]['proctime']['running'] = 0;          // mean value of execution time
                $data[$hostid][$year][$month][$day][$hour]['proctime']['count'] = 0;            // number of jobs
                $data[$hostid][$year][$month][$day][$hour]['proctime']['minimum'] = $process;   // minimum time from submit to finished
                $data[$hostid][$year][$month][$day][$hour]['proctime']['maximum'] = 0;          // maximum time from submit to finished
        }
        $data[$hostid][$year][$month][$day][$hour]['proctime']['count']++;
        $data[$hostid][$year][$month][$day][$hour]['proctime']['waiting'] = floating_mean_value($data[$hostid][$year][$month][$day][$hour]['proctime']['count'], $data[$hostid][$year][$month][$day][$hour]['proctime']['waiting'], $waiting);
        $data[$hostid][$year][$month][$day][$hour]['proctime']['running'] = floating_mean_value($data[$hostid][$year][$month][$day][$hour]['proctime']['count'], $data[$hostid][$year][$month][$day][$hour]['proctime']['running'], $running);
        if ($process < $data[$hostid][$year][$month][$day][$hour]['proctime']['minimum']) {
                $data[$hostid][$year][$month][$day][$hour]['proctime']['minimum'] = $process;
        }
        if ($process > $data[$hostid][$year][$month][$day][$hour]['proctime']['maximum']) {
                $data[$hostid][$year][$month][$day][$hour]['proctime']['maximum'] = $process;
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
        if ($options->qmode == LIST_QUEUE_PER_HOSTID) {
                $jobqueue = array();
                get_queued_jobs($jobqueue);
        }

        $handle = @opendir($hiddir);
        if ($handle) {
                if ($options->debug) {
                        printf("debug: processing job directories for host ID %s\n", $hostid);
                }
                while (($file = readdir($handle)) !== false) {
                        $jobdir = sprintf("%s/%s", $hiddir, $file);
                        if ($file != "." && $file != ".." && is_dir($jobdir)) {
                                // 
                                // Should this directory be collected?
                                // 
                                $collected = sprintf("%s/collected", $jobdir);
                                if (!$options->force && file_exists($collected)) {
                                        if ($options->debug) {
                                                printf("debug: directory %s already collected (skipped)\n", $file);
                                        }
                                        continue;
                                }
                                if ($options->debug) {
                                        printf("debug: processing job directory %s\n", $file);
                                }

                                // 
                                // Fetch queued jobs in each loop?
                                // 
                                if ($options->qmode == LIST_QUEUE_PER_JOBDIR) {
                                        $jobqueue = array();
                                        get_queued_jobs($jobqueue);
                                }

                                // 
                                // Get state of job:
                                // 
                                $state = collect_job_state($jobdir, $jobqueue);
                                if ($options->debug) {
                                        printf("debug: job directory %s is in %s state\n", $file, $state);
                                }
                                if ($state == "pending" || $state == "running") {
                                        if ($options->debug) {
                                                printf("debug: directory %s contains a pending or running job (skipped)", $file);
                                        }
                                        continue;
                                }

                                // 
                                // Get queued, started and finished timestamps:
                                // 
                                $queued = collect_file_content(sprintf("%s/queued", $jobdir), 0);
                                $started = collect_file_content(sprintf("%s/started", $jobdir), 0);
                                $finished = collect_file_content(sprintf("%s/finished", $jobdir), 0);

                                if ($queued == 0) {
                                        printf(sprintf("warning: queued == 0 in jobs/%s/%s (skipped)\n", $hostid, $file));
                                        continue;
                                }

                                // 
                                // Get datetime parts of queued time:
                                // 
                                $date = getdate($queued);
                                $year = $date['year'];
                                $month = sprintf("%02d", $date['mon']);
                                $day = sprintf("%02d", $date['mday']);
                                $hour = sprintf("%02d", $date['hours']);

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
                                if ($finished > 0 && ($state == "success" || $state == "warning")) {
                                        collect_process_accounting($hostid, $data, $queued, $started, $finished, $year, $month, $day, $hour);
                                        collect_process_accounting("all", $data, $queued, $started, $finished, $year, $month, $day, $hour);
                                }

                                // 
                                // Call user supplied statistics collection hook function if defined.
                                // 
                                if (function_exists("collect_data_hook")) {
                                        collect_data_hook($hostid, $data, $jobdir, $year, $month, $day, $hour);
                                        collect_data_hook("all", $data, $jobdir, $year, $month, $day, $hour);
                                }

                                // 
                                // Flag directory as collected.
                                // 
                                file_put_contents($collected, time());
                        }
                }
                closedir($handle);
        } else {
                die(sprintf("%s: failed reading directory %s\n", $options->prog, $hiddir));
        }
}

// 
// Recalculate the data array. When this function returns, the submit count
// has been recalculated and the state should have been adjusted.
// 
// Some node data can only be accurate calculated from job directories, that
// might be missing. Other data is custom (user defined) created from hooks,
// that data is copied verbatime.
// 
function collect_recalc_data(&$data, $options)
{
        $arr = array();

        foreach ($data as $key => $val) {       // key == hostid
                if ($key == "all") {
                        continue;
                }
                collect_recalc_hostid($arr, $key, $val, $options);
        }
}

function collect_recalc_hostid(&$arr, $hostid, $data, $options)
{
        if ($options->debug) {
                printf("debug: recalc hostid %s\n", $hostid);
        }

        foreach ($data as $key => $val) {
                if ($key == "submit") {
                        continue;
                }
                if (is_numeric($key)) {
                        collect_recalc_year($arr, $hostid, $key, $val, $options);
                } else {
                        collect_recalc_validate($key, $val, $options);
                        $arr[$hostid][$key] = $val;
                }
        }
}

function collect_recalc_year(&$arr, $hostid, $year, $data, $options)
{
        if ($options->debug) {
                printf("debug: recalc year %s\n", $year);
        }

        foreach ($data as $key => $val) {
                if ($key == "submit") {
                        continue;
                }
                if (is_numeric($key)) {
                        collect_recalc_month($arr, $hostid, $year, $key, $val, $options);
                } else {
                        collect_recalc_validate($key, $val, $options);
                        $arr[$hostid][$year][$key] = $val;
                }
        }
}

function collect_recalc_month(&$arr, $hostid, $year, $month, $data, $options)
{
        if ($options->debug) {
                printf("debug: recalc month %s\n", $month);
        }

        foreach ($data as $key => $val) {
                if ($key == "submit") {
                        continue;
                }
                if (is_numeric($key)) {
                        collect_recalc_day($arr, $hostid, $year, $month, $key, $val, $options);
                } else {
                        collect_recalc_validate($key, $val, $options);
                        $arr[$hostid][$year][$month][$key] = $val;
                }
        }
}

function collect_recalc_day(&$arr, $hostid, $year, $month, $day, $data, $options)
{
        if ($options->debug) {
                printf("debug: recalc day %s\n", $day);
        }

        foreach ($data as $key => $val) {
                if ($key == "submit") {
                        continue;
                }
                if (is_numeric($key)) {
                        collect_recalc_hour($arr, $hostid, $year, $month, $day, $key, $val, $options);
                } else {
                        collect_recalc_validate($key, $val, $options);
                        $arr[$hostid][$year][$month][$day][$key] = $val;
                }
        }
}

function collect_recalc_hour(&$arr, $hostid, $year, $month, $day, $hour, $data, $options)
{
        if ($options->debug) {
                printf("debug: recalc hour %s\n", $hour);
        }

        foreach ($data as $key => $val) {
                if ($key == "submit") {
                        collect_submit_count($hostid, $arr, $year, $month, $day, $hour);
                        collect_submit_count("all", $arr, $year, $month, $day, $hour);
                } else {
                        collect_recalc_validate($key, $val, $options);
                        $arr[$hostid][$year][$month][$day][$hour][$key] = $val;
                }
        }
}

// 
// Check if hash array $val contains the required set of array keys for given
// section $key. The $check value defines how missing keys are handled, see
// REPAIR_CHECK_XXX konstants.
// 
function collect_recalc_validate($key, &$val, $options, $check = REPAIR_CHECK_STRICT)
{
        $req = array();

        switch ($key) {
                case "submit":
                        $req = array("count" => 1);
                        break;
                case "proctime":
                        $req = array("waiting" => 0, "running" => 0, "count"   => 1, "minimum" => 0, "maximum" => 0);
                        break;
                case "state":
                        $check = REPAIR_CHECK_IGNORE;   // Only non-zero keys are usually set.
                        $req = array("success" => 0, "warning" => 0, "crashed" => 0);
                        break;
                default:
                        if ($options->verbose) {
                                printf("Skip validation of section %s, probably custom data.\n", $key);
                        }
                        return;
        }

        foreach ($req as $k => $v) {
                if (in_array($k, array_keys($val))) {
                        continue;
                }
                switch ($check) {
                        case REPAIR_CHECK_STRICT:
                                die(sprintf("%s: missing key %s in section %s\n", $options->prog, $k, $key));
                                break;
                        case REPAIR_CHECK_INSERT:
                                $val[$k] = $v;  // Set default value.
                                break;
                        case REPAIR_CHECK_IGNORE:
                                if ($options->verbose) {
                                        printf("Ignoring missing key %s in section %s\n", $k, $key);
                                }
                                break;
                }
        }
}

// 
// Process summary.dat files and store entries in the data array. Duplicate
// entries merge mode is defined in options->repair.
// 
// $statdir:  the statistics directory root path.
// $options:  program options.
// $data:     statistics data array.
// 
function collect_read_data($statdir, &$data, $options)
{
        // 
        // Process summary.dat before any sub directories.
        // 
        $path = sprintf("%s/summary.dat", $statdir);
        if (file_exists($path)) {
                collect_file_data($path, $data, $options);
        }

        if (($handle = opendir($statdir))) {
                while (($file = readdir($handle)) !== false) {
                        $path = sprintf("%s/%s", $statdir, $file);
                        if ($file == "." || $file == "..") {
                                continue;
                        }
                        if (is_dir($path)) {
                                collect_read_data($path, $data, $options);
                        }
                }
                closedir($handle);
        } else {
                die(sprintf("%s: failed reading directory %s\n", $options->prog, $statdir));
        }
}

// 
// Read content from summary.dat file.
// 
function collect_file_data($file, &$data, $options)
{
        if ($options->debug) {
                printf("debug: checking data from %s\n", $file);
        }

        // 
        // Get sect/year/month/day/hour from file path:
        // 
        $pattern = "%stat/(.*?)/summary.dat%";
        $matches = array();
        if (!preg_match($pattern, $file, $matches)) {
                die(sprintf("%s: failed match '%s' against pattern '%s'\n", $options->prog, $file, $pattern));
        }

        $p = explode("/", $matches[1]);
        switch (count($p)) {
                case 1:
                        if (collect_file_check_insert(isset($data[$p[0]]), $options->repair)) {
                                collect_file_insert($data[$p[0]], $file, $options);
                        }
                        break;
                case 2:
                        if (collect_file_check_insert(isset($data[$p[0]][$p[1]]), $options->repair)) {
                                collect_file_insert($data[$p[0]][$p[1]], $file, $options);
                        }
                        break;
                case 3:
                        if (collect_file_check_insert(isset($data[$p[0]][$p[1]][$p[2]]), $options->repair)) {
                                collect_file_insert($data[$p[0]][$p[1]][$p[2]], $file, $options);
                        }
                        break;
                case 4:
                        if (collect_file_check_insert(isset($data[$p[0]][$p[1]][$p[2]][$p[3]]), $options->repair)) {
                                collect_file_insert($data[$p[0]][$p[1]][$p[2]][$p[3]], $file, $options);
                        }
                        break;
                case 5:
                        if (collect_file_check_insert(isset($data[$p[0]][$p[1]][$p[2]][$p[3]][$p[4]]), $options->repair)) {
                                collect_file_insert($data[$p[0]][$p[1]][$p[2]][$p[3]][$p[4]], $file, $options);
                        }
                        break;
                default:
                        die(sprintf("%s: too many parts in stated filename.\n", $options->prog));
        }
}

// 
// Return true if entry in data array should be set.
// 
function collect_file_check_insert($isset, $mode)
{
        if ($mode == REPAIR_MODE_REPLACE && $isset == true) {
                return true;
        }
        if ($mode == REPAIR_MODE_MISSING && $isset == false) {
                return true;
        }
        return false;
}

// 
// Insert content of file (summary.dat) in data array entry.
// 
function collect_file_insert(&$entry, $file, $options)
{
        if ($options->debug) {
                printf("debug: inserting data from %s\n", $file);
        }

        if (($handle = fopen($file, "r"))) {
                $sect = null;
                while ($str = fgets($handle)) {
                        $pattern = array(
                                "%\[(.*)\]%",
                                "%(\w+)\s*=\s*(\w+)%"
                        );
                        foreach ($pattern as $p) {
                                $match = array();
                                if (preg_match($p, $str, $match)) {
                                        if (count($match) == 2) {
                                                $sect = $match[1];
                                                if ($options->debug) {
                                                        printf("debug: matched section '%s'.\n", $sect);
                                                }
                                        } elseif (count($match) == 3) {
                                                if (!isset($sect)) {
                                                        die(sprintf("%s: section is not set.\n", $options->prog));
                                                }
                                                if ($options->debug) {
                                                        printf("debug: inserting [%s][%s] = %d.\n", $sect, $match[1], $match[2]);
                                                }
                                                $entry[$sect][$match[1]] = $match[2];
                                        }
                                }
                        }
                }
        } else {
                die(sprintf("%s: failed open %s\n", $options->prog, $file));
        }
}

// 
// Recursive write data files (text) from collected statistics.
// 
function collect_flush_data($topdir, $data, $options)
{
        $summary = sprintf("%s/summary.dat", $topdir);
        if (file_exists($summary)) {
                unlink($summary);
        }
        foreach ($data as $sect => $arr) {
                if (is_numeric($sect)) {
                        $subdir = sprintf("%s/%s", $topdir, $sect);
                        if (!file_exists($subdir)) {
                                if (!mkdir($subdir)) {
                                        die(sprintf("%s: failed create directory %s\n", $options->prog, $subdir));
                                }
                        }
                        collect_flush_data($subdir, $arr, $options);
                } else {
                        $handle = fopen($summary, "a");
                        if (!$handle) {
                                die(sprintf("%s: failed append data to file %s\n", $options->prog, $summary));
                        }
                        if (function_exists("fprintf")) {
                                fprintf($handle, "[%s]\n", $sect);
                                foreach ($arr as $key => $val) {
                                        fprintf($handle, "%s = %s\n", $key, $val);
                                }
                                fprintf($handle, "\n");
                        } else {
                                fwrite($handle, sprintf("[%s]\n", $sect));
                                foreach ($arr as $key => $val) {
                                        fwrite($handle, sprintf("%s = %s\n", $key, $val));
                                }
                                fwrite($handle, "\n");
                        }
                        fclose($handle);
                }
        }
}

// 
// Write collected statistics to the filesystem.
// 
function collect_flush_stats($statdir, $statdata, $options)
{
        foreach ($statdata as $hostid => $arr) {
                $subdir = sprintf("%s/%s", $statdir, $hostid);
                if (!file_exists($subdir)) {
                        if ($options->verbose) {
                                printf("updating statistics for %s\n", $hostid);
                        }
                        if (!mkdir($subdir)) {
                                die(sprintf("%s: failed create directory %s\n", $options->prog, $subdir));
                        }
                }
                collect_flush_data($subdir, $arr, $options);
        }
}

// 
// Draws a bar plot.
// 
function graph_draw_barplot($labels, $values, $image, $title, $subtitle, $colors, $callback = null)
{
        $width = 460;
        $height = 210;

        if (count($values) > 12) {
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
        $graph->legend->SetFillColor(JPGRAPH_LEGEND_BACKGROUND_COLOR);
        $graph->legend->SetColor(JPGRAPH_LEGEND_FOREGROUND_COLOR, JPGRAPH_LEGEND_BORDER_COLOR);
        $graph->footer->right->Set(sprintf("Generated: %s", strftime("%Y-%m-%d")));
        $graph->footer->right->SetColor(JPGRAPH_NOTES_FOREGROUND_COLOR);

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

        $graph->xaxis->title->SetFont(FF_FONT1, FS_NORMAL);
        $graph->xaxis->title->SetColor(JPGRAPH_NOTES_FOREGROUND_COLOR);

        // 
        // Create a bar pot
        // 
        $bplot = new BarPlot($values);
        $bplot->SetFillGradient($colors['color']['start'], $colors['color']['end'], GRAD_HOR);
        $bplot->SetWidth(0.5);
        if (isset($colors['color']['outline'])) {
                $bplot->SetColor($colors['color']['outline']);
        }
        if (isset($colors['color']['shadow'])) {
                $bplot->SetShadow($colors['color']['shadow']);
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
        $bplot->value->SetColor($colors['text']['positive'], $colors['text']['negative']);
        $graph->Add($bplot);

        // 
        // Any last minute changes?
        // 
        if ($callback) {
                $callback($graph);
        }

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
        $title = "Total number of submits";
        $total = 0;
        $image = sprintf("%s/submit.png", $graphdir);

        $labels = array();
        $values = array();
        $colors = array(
                "color" => array(
                        "start"   => "navy",
                        "end"     => "lightsteelblue",
                        "outline" => "darkblue"
                ),
                "text"    => array(
                        "positive" => "black",
                        "negative" => "lightgray"
                )
        );

        foreach ($data as $year => $data1) {
                if (is_numeric($year)) {
                        foreach ($data1 as $sect => $value) {
                                if ($sect == "submit") {
                                        if (count($labels) == 0 || $year > $labels[0]) {
                                                array_push($values, $value['count']);
                                                array_push($labels, $year);
                                        } else {
                                                array_unshift($values, $value['count']);
                                                array_unshift($labels, $year);
                                        }
                                        $total += $value['count'];
                                }
                        }
                }
        }

        // 
        // Add one extra year before first year and after last year to prevent
        // odd looking graphics when only one year statistics is present.
        // 
        if (count($labels) < 3) {
                array_unshift($values, 0);
                array_unshift($labels, $labels[0] - 1);
                array_push($values, 0);
                array_push($labels, $labels[count($labels) - 1] + 1);
        }

        if ($options->debug) {
                printf("debug: creating graphic file %s\n", $image);
        }
        graph_draw_barplot($labels, $values, $image, $title, sprintf("total %d submits", $total), $colors);
}

// 
// Create graph of submits for one year for hostid (that might be all)
// 
function graph_yearly_submit($graphdir, $hostid, $options, $timestamp, $data)
{
        $title = sprintf("Number of submits %s", strftime("%Y", $timestamp));
        $total = 0;
        $image = sprintf("%s/submit.png", $graphdir);

        $labels = array();
        $values = array();
        $colors = array(
                "color" => array(
                        "start"   => "orange",
                        "end"     => "yellow",
                        "outline" => "red"
                ),
                "text"    => array(
                        "positive" => "black",
                        "negative" => "lightgray"
                )
        );

        // 
        // Initilize data.
        // 
        for ($i = 0; $i < 12; ++$i) {
                $values[$i] = 0;
                $labels[$i] = strftime("%b", mktime(0, 0, 0, $i + 1, 1, 1));
        }

        foreach ($data as $month => $data1) {
                if (is_numeric($month)) {
                        foreach ($data1 as $sect => $value) {
                                if ($sect == "submit") {
                                        $values[intval($month) - 1] = $value['count'];
                                        $total += $value['count'];
                                }
                        }
                }
        }

        if ($options->debug) {
                printf("debug: creating graphic file %s\n", $image);
        }
        graph_draw_barplot($labels, $values, $image, $title, sprintf("total %d submits", $total), $colors);
}

// 
// Create graph of submits for one month for hostid (that might be all)
// 
function graph_monthly_submit($graphdir, $hostid, $options, $timestamp, $data)
{
        $title = sprintf("Number of submits for %s", strftime("%B %Y", $timestamp));
        $total = 0;
        $image = sprintf("%s/submit.png", $graphdir);

        $labels = array();
        $values = array();
        $colors = array(
                "color" => array(
                        "start"   => "green",
                        "end"     => "yellow",
                        "outline" => "darkgreen"
                ),
                "text"    => array(
                        "positive" => "black",
                        "negative" => "lightgray"
                )
        );

        // 
        // Initilize data.
        // 
        for ($i = 0; $i < date('t', $timestamp); ++$i) {
                $values[$i] = 0;
                $labels[$i] = $i + 1;
        }

        foreach ($data as $day => $data1) {
                if (is_numeric($day)) {
                        foreach ($data1 as $sect => $value) {
                                if ($sect == "submit") {
                                        $values[intval($day) - 1] = $value['count'];
                                        $total += $value['count'];
                                }
                        }
                }
        }

        if ($options->debug) {
                printf("debug: creating graphic file %s\n", $image);
        }
        graph_draw_barplot($labels, $values, $image, $title, sprintf("total %d submits", $total), $colors);
}

// 
// Create graph of submits for one day for hostid (that might be all)
// 
function graph_daily_submit($graphdir, $hostid, $options, $timestamp, $data)
{
        $title = sprintf("Number of submits for %s", strftime("%Y-%m-%d", $timestamp));
        $total = 0;
        $image = sprintf("%s/submit.png", $graphdir);

        $labels = array();
        $values = array();
        $colors = array(
                "color" => array(
                        "start"   => "purple",
                        "end"     => "red",
                        "outline" => "pink"
                ),
                "text"    => array(
                        "positive" => "black",
                        "negative" => "lightgray"
                )
        );

        // 
        // Initilize data.
        // 
        for ($i = 0; $i < 24; ++$i) {
                $values[$i] = 0;
                $labels[$i] = sprintf("%02d", $i);
        }

        foreach ($data as $hour => $data1) {
                if (is_numeric($hour)) {
                        foreach ($data1 as $sect => $value) {
                                if ($sect == "submit") {
                                        $values[intval($hour)] = $value['count'];
                                        $total += $value['count'];
                                }
                        }
                }
        }

        if ($options->debug) {
                printf("debug: creating graphic file %s\n", $image);
        }
        graph_draw_barplot($labels, $values, $image, $title, sprintf("total %d submits", $total), $colors);
}

// 
// Draws a diagram of process time data.
// 
function graph_draw_proctime($labels, $values, $image, $title, $subtitle, $colors)
{
        $width = 560;
        $height = 260;

        if (count($labels) > 5 && count($labels) <= 12) {
                $width += count($labels) * 10;
        }
        if (count($labels) > 12) {
                $width += count($labels) * 16;
        }

        // 
        // The zeroes array is used as a placeholder when grouping bars.
        // 
        $zeroes = array();
        for ($i = 0; $i < count($labels); ++$i) {
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
        $graph->legend->SetFillColor(JPGRAPH_LEGEND_BACKGROUND_COLOR);
        $graph->legend->SetColor(JPGRAPH_LEGEND_FOREGROUND_COLOR, JPGRAPH_LEGEND_BORDER_COLOR);
        $graph->footer->right->Set(sprintf("Generated: %s", strftime("%Y-%m-%d")));
        $graph->footer->right->SetColor(JPGRAPH_NOTES_FOREGROUND_COLOR);

        // 
        // Add some grace to the top so that the scale doesn't
        // end exactly at the max value. 
        // 
        $graph->yaxis->scale->SetGrace(20);
        $graph->y2axis->scale->SetGrace(25);

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

        $graph->xaxis->title->SetFont(FF_FONT1, FS_NORMAL);
        $graph->xaxis->title->SetColor(JPGRAPH_NOTES_FOREGROUND_COLOR);

        // 
        // Create the bar and line plots
        // 
        $b1plot = new BarPlot($values['waiting']);
        $b1plot->Setlegend("Waiting");

        $b2plot = new BarPlot($values['running']);
        $b2plot->SetLegend("Running");

        $b3plot = new BarPlot($values['maximum']);
        $b3plot->SetLegend("Maximum");

        $b4plot = new BarPlot($values['minimum']);
        $b4plot->SetLegend("Minimum");

        $z0plot = new BarPlot($zeroes);

        if (JPGRAPH_ENABLE_GRADIENTS & JPGRAPH_GRADIENT_PROCTIME) {
                $b1plot->SetFillGradient($colors['color']['waiting']['start'], $colors['color']['waiting']['end'], GRAD_MIDVER);
                $b2plot->SetFillGradient($colors['color']['running']['start'], $colors['color']['running']['end'], GRAD_MIDVER);
                $b3plot->SetFillGradient($colors['color']['maximum']['start'], $colors['color']['maximum']['end'], GRAD_MIDVER);
                $b4plot->SetFillGradient($colors['color']['minimum']['start'], $colors['color']['minimum']['end'], GRAD_MIDVER);
                $b1plot->SetColor($colors['color']['waiting']['outline']);
                $b2plot->SetColor($colors['color']['running']['outline']);
                $b3plot->SetColor($colors['color']['maximum']['outline']);
                $b4plot->SetColor($colors['color']['minimum']['outline']);
        } else {
                $b1plot->SetFillColor($colors['color']['waiting']['start']);
                $b2plot->SetFillColor($colors['color']['running']['start']);
                $b3plot->SetFillColor($colors['color']['maximum']['start']);
                $b4plot->SetFillColor($colors['color']['minimum']['start']);
        }

        // 
        // Create the grouped bar plot and add it to the graph:
        // 
        $abplot = new AccBarPlot(array($b1plot, $b2plot));
        $abplot->value->SetColor($colors['text']['positive'], $colors['text']['negative']);
        $abplot->value->SetAngle(90);
        $abplot->value->show();

        $y1plot = new GroupBarPlot(array($abplot, $z0plot, $z0plot));
        $y2plot = new GroupBarPlot(array($z0plot, $b3plot, $b4plot));
        $y1plot->SetWidth(0.97);
        $y2plot->SetWidth(0.97);

        $b3plot->value->SetColor($colors['text']['positive'], $colors['text']['negative']);
        $b4plot->value->SetColor($colors['text']['positive'], $colors['text']['negative']);
        $b3plot->value->SetAngle(90);
        $b4plot->value->SetAngle(90);
        $b3plot->value->show();
        $b4plot->value->show();

        $graph->Add($y1plot);
        $graph->AddY2($y2plot);

        // 
        // Create the graph
        // 
        $graph->Stroke($image);
}

// 
// Returns array with color definitions for proctime data.
// 
function proctime_colors()
{
        $colors = array(
                "waiting" => array(
                        "start"   => "orange",
                        "end"     => "yellow",
                        "outline" => "red"
                ),
                "running" => array(
                        "start"   => "darkgreen",
                        "end"     => "green",
                        "outline" => "darkgreen"
                ),
                "minimum" => array(
                        "start"   => "darkgray",
                        "end"     => "gray",
                        "outline" => "darkgray"
                ),
                "maximum" => array(
                        "start"   => "red",
                        "end"     => "orange",
                        "outline" => "darkred"
                ),
                "count"   => array(
                        "start"   => "darkblue",
                        "end"     => "blue",
                        "outline" => "lightblue"
                )
        );
        return $colors;
}

// 
// Create graph of total process time for hostid (that might be all)
// for all years.
// 
function graph_total_proctime($graphdir, $hostid, $options, $data)
{
        $title = "Total process time (avarage)";
        $total = 0;
        $image = sprintf("%s/proctime.png", $graphdir);

        $labels = array();
        $values = array(
                "waiting" => array(),
                "running" => array(),
                "count" => array(),
                "minimum" => array(),
                "maximum" => array()
        );
        $colors = array(
                "color" => proctime_colors(),
                "text"  => array(
                        "positive" => "black",
                        "negative" => "lightgray"
                )
        );

        // 
        // The data can be in any order due to locale setting. Don't trust
        // it to be in increasing numeric order.
        // 
        foreach ($data as $year => $data1) {
                if (is_numeric($year)) {
                        foreach ($data1 as $sect => $value) {
                                if ($sect == "proctime") {
                                        if (count($labels) == 0 || $year > $labels[0]) {
                                                foreach (array_keys($values) as $key) {
                                                        array_push($values[$key], $value[$key]);
                                                }
                                                array_push($labels, $year);
                                        } else {
                                                foreach (array_keys($values) as $key) {
                                                        array_unshift($values[$key], $value[$key]);
                                                }
                                                array_unshift($labels, $year);
                                        }
                                        $total += $value['count'];
                                }
                        }
                }
        }

        // 
        // Add one extra year before first year and after last year to prevent
        // odd looking graphics when only one year statistics is present.
        // 
        if (count($labels) < 3) {
                foreach (array_keys($values) as $key) {
                        array_unshift($values[$key], 0);
                        array_push($values[$key], 0);
                }
                array_unshift($labels, $labels[0] - 1);
                array_push($labels, $labels[count($labels) - 1] + 1);
        }

        if ($options->debug) {
                printf("debug: creating graphic file %s\n", $image);
        }
        graph_draw_proctime($labels, $values, $image, $title, sprintf("totally %d finished jobs counted", $total), $colors);
}

// 
// Create graph of process time for one year for hostid (that might be all)
// 
function graph_yearly_proctime($graphdir, $hostid, $options, $timestamp, $data)
{
        $title = sprintf("Process time %s (avarage)", strftime("%Y", $timestamp));
        $total = 0;
        $image = sprintf("%s/proctime.png", $graphdir);

        $labels = array();
        $values = array(
                "waiting" => array(),
                "running" => array(),
                "count" => array(),
                "minimum" => array(),
                "maximum" => array()
        );
        $colors = array(
                "color" => proctime_colors(),
                "text"  => array(
                        "positive" => "black",
                        "negative" => "lightgray"
                )
        );

        // 
        // Initilize data.
        // 
        for ($i = 0; $i < 12; ++$i) {
                foreach (array_keys($values) as $key) {
                        $values[$key][$i] = 0;
                }
                $labels[$i] = strftime("%b", mktime(0, 0, 0, $i + 1, 1, 1));
        }

        foreach ($data as $month => $data1) {
                if (is_numeric($month)) {
                        foreach ($data1 as $sect => $value) {
                                if ($sect == "proctime") {
                                        $index = intval($month) - 1;
                                        foreach (array_keys($values) as $key) {
                                                $values[$key][$index] = $value[$key];
                                        }
                                        $total += $value['count'];
                                }
                        }
                }
        }

        if ($options->debug) {
                printf("debug: creating graphic file %s\n", $image);
        }
        graph_draw_proctime($labels, $values, $image, $title, sprintf("totally %d finished jobs counted", $total), $colors);
}

// 
// Create graph of process time for one month for hostid (that might be all)
// 
function graph_monthly_proctime($graphdir, $hostid, $options, $timestamp, $data)
{
        $title = sprintf("Process time %s (avarage)", strftime("%B %Y", $timestamp));
        $total = 0;
        $image = sprintf("%s/proctime.png", $graphdir);

        $labels = array();
        $values = array(
                "waiting" => array(),
                "running" => array(),
                "count" => array(),
                "minimum" => array(),
                "maximum" => array()
        );
        $colors = array(
                "color" => proctime_colors(),
                "text"  => array(
                        "positive" => "black",
                        "negative" => "lightgray"
                )
        );

        // 
        // Initilize data.
        // 
        for ($i = 0; $i < date('t', $timestamp); ++$i) {
                foreach (array_keys($values) as $key) {
                        $values[$key][$i] = 0;
                }
                $labels[$i] = $i + 1;
        }

        foreach ($data as $day => $data1) {
                if (is_numeric($day)) {
                        foreach ($data1 as $sect => $value) {
                                if ($sect == "proctime") {
                                        $index = intval($day) - 1;
                                        foreach (array_keys($values) as $key) {
                                                $values[$key][$index] = $value[$key];
                                        }
                                        $total += $value['count'];
                                }
                        }
                }
        }

        if ($options->debug) {
                printf("debug: creating graphic file %s\n", $image);
        }
        graph_draw_proctime($labels, $values, $image, $title, sprintf("totally %d finished jobs counted", $total), $colors);
}

// 
// Create graph of process time for one day for hostid (that might be all)
// 
function graph_daily_proctime($graphdir, $hostid, $options, $timestamp, $data)
{
        $title = sprintf("Process time %s (avarage)", strftime("%Y-%m-%d", $timestamp));
        $total = 0;
        $image = sprintf("%s/proctime.png", $graphdir);

        $labels = array();
        $values = array(
                "waiting" => array(),
                "running" => array(),
                "count" => array(),
                "minimum" => array(),
                "maximum" => array()
        );
        $colors = array(
                "color" => proctime_colors(),
                "text"  => array(
                        "positive" => "black",
                        "negative" => "lightgray"
                )
        );

        // 
        // Initilize data.
        // 
        for ($i = 0; $i < 24; ++$i) {
                foreach (array_keys($values) as $key) {
                        $values[$key][$i] = 0;
                }
                $labels[$i] = sprintf("%02d", $i);
        }

        foreach ($data as $hour => $data1) {
                if (is_numeric($hour)) {
                        foreach ($data1 as $sect => $value) {
                                if ($sect == "proctime") {
                                        foreach (array_keys($values) as $key) {
                                                $values[$key][intval($hour)] = $value[$key];
                                        }
                                        $total += $value['count'];
                                }
                        }
                }
        }

        if ($options->debug) {
                printf("debug: creating graphic file %s\n", $image);
        }
        graph_draw_proctime($labels, $values, $image, $title, sprintf("totally %d finished jobs counted", $total), $colors);
}

// 
// 
// 
function graph_draw_pieplot($labels, $values, $image, $title, $colors)
{
        $graph = new PieGraph(320, 200, "auto");

        $graph->title->Set($title);
        $graph->title->SetFont(FF_FONT2, FS_BOLD);
        $graph->footer->right->Set(sprintf("Generated: %s", strftime("%Y-%m-%d")));
        $graph->footer->right->SetColor(JPGRAPH_NOTES_FOREGROUND_COLOR);
        $graph->legend->SetFillColor(JPGRAPH_LEGEND_BACKGROUND_COLOR);
        $graph->legend->SetColor(JPGRAPH_LEGEND_FOREGROUND_COLOR, JPGRAPH_LEGEND_BORDER_COLOR);

        $graph->SetFrame(true, JPGRAPH_FRAME_FOREGROUND_COLOR, JPGRAPH_FRAME_BORDER_WIDTH);
        if (JPGRAPH_ENABLE_ANTIALIASING) {
                $graph->SetAntiAliasing();
        }

        $pie = new PiePlot($values);
        $pie->SetLegends($labels);
        $pie->SetCenter(0.35);
        $pie->SetSliceColors($colors);
        $pie->value->SetColor(JPGRAPH_XAXIS_LABEL_COLOR);

        $graph->Add($pie);
        $graph->Stroke($image);
}

// 
// Generate state graphics for hostid (might be all).
// 
function graph_total_state($graphdir, $hostid, $options, $data)
{
        $title = "Exit status of jobs";
        $image = sprintf("%s/state.png", $graphdir);

        $labels = array();
        $values = array();
        $colors = array();

        $piecol = array(
                "success" => "green",
                "warning" => "yellow",
                "error"   => "red",
                "crashed" => "gray"
        );

        foreach ($data['state'] as $key => $val) {
                array_push($colors, $piecol[$key]);
                array_push($values, $val);
                array_push($labels, ucfirst($key));
        }
        if ($options->debug) {
                printf("debug: creating graphic file %s\n", $image);
        }
        graph_draw_pieplot($labels, $values, $image, $title, $colors);
}

// 
// Makes array data smoother by inserting $points extra values between
// each value in the array. This filter creates a parable with every
// second value slightly disturbed from its calculated value. Note 
// that this filter only guarantee that the max value in each step is 
// preserved.
// 
function apply_parable_filter(&$arr, $points)
{
        // 
        // We need to do some ugly hacks because not all arrays are zero-indexed.
        // It is taken for granted that the array we process have keys that forms
        // an arithmetric serie.
        // 
        $data = array();
        $keys = array_keys($arr);
        $step = floor(($keys[1] - $keys[0]) / $points);

        for ($i = 0; $i < count($keys); $i++) {
                $curr = $arr[$keys[$i]];
                $next = isset($keys[$i + 1]) ? $arr[$keys[$i + 1]] : $curr;
                for ($j = 1; $j <= $points; $j++) {
                        // 
                        // Calculate the parable:
                        // 
                        $x = $j / $points;
                        $y = $curr - 4 * $curr * ( $x * $x - $x + 0.25 );
                        // 
                        // Add some fuzz to non-zero values:
                        // 
                        if ($y != 0) {
                                if ($j % 2 == 1) {
                                        $y -= rand(0, $y) * PARABLE_FILTER_DIST;
                                }
                        }
                        // 
                        // Smooth out to next value:
                        // 
                        if ($j == $points && $next != 0) {
                                $y += PARABLE_FILTER_DIST * $next * $j / $points;
                        }
                        if ($keys[0] == 0) {
                                $data[] = $y;
                        } else {
                                $data[$keys[$i] + ($j - 1) * $step] = $y;
                        }
                }
        }
        $arr = $data;
}

// 
// This filter makes a random distibution of the value in each interval.
// 
function apply_distrib_filter(&$arr, $points)
{
        $data = array();
        $keys = array_keys($arr);
        $step = floor(($keys[1] - $keys[0]) / $points);

        for ($i = 0; $i < count($keys); $i++) {
                $curr = $arr[$keys[$i]];
                $randmax = floor($curr / $points);
                if ($randmax == 0) {
                        $randmax = ($curr == 0) ? 0 : 1;
                }
                $used = 0;
                for ($j = 1; $j <= $points; $j++) {
                        if ($j == $points) {
                                $rand = $curr - $used;
                                if ($rand < 0) {
                                        $rand = 0;
                                }
                        } else {
                                $rand = rand(0, $randmax);
                                $used += $rand;
                        }
                        if ($keys[0] == 0) {
                                $data[] = $rand;
                        } else {
                                $data[$keys[$i] + ($j - 1) * $step] = $rand;
                        }
                }
        }
        $arr = $data;
}

// 
// Converts a timestamp for daily data.
// 
function timestamp_for_daily($stamp)
{
        return date('H:i', $stamp);
}

// 
// Converts a timestamp for monthly data.
// 
function timestamp_for_monthly($stamp)
{
        return date('j', $stamp);
}

// 
// Convert a timestamp for yearly data.
// 
function timestamp_for_yearly($stamp)
{
        return date('M', $stamp);
}

// 
// Convert a weekly key value to weekday.
// 
function timestamp_for_weekly($value)
{
        $days = array("Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun");
        return $days[$value / 24];
}

// 
// Convert an hour value to hh:mm string.
// 
function timestamp_for_hourly($value)
{
        $value /= HOURLY_FUZZY_FILTER_POINTS;
        if ($value == 24) {
                return "24:00";
        }
        return date('H:i', mktime($value, 0));
}

// 
// Convert an timestamp from the total array to year and month.
// 
function timestamp_for_total($stamp)
{
        $date = getdate($stamp);
        if ($date['mday'] == 1) {
                if (($date['mon'] - 1) % 3 == 0) {
                        if ($date['mon'] == 1)
                                return date('Y', $stamp);
                        else
                                return date('M', $stamp);
                }
        }
}

// 
// Plot system load data.
// 
function graph_draw_system_load($data, $image, $title, $mode, $options)
{
        if ($mode == "hourly") {
                if (HOURLY_FILTER_TYPE == FILTER_TYPE_PARABLE) {
                        apply_parable_filter($data['submit'], HOURLY_FUZZY_FILTER_POINTS);
                        apply_parable_filter($data['waiting'], HOURLY_FUZZY_FILTER_POINTS);
                        apply_parable_filter($data['running'], HOURLY_FUZZY_FILTER_POINTS);
                } else if (HOURLY_FILTER_TYPE == FILTER_TYPE_DISTRIB) {
                        apply_distrib_filter($data['submit'], HOURLY_FUZZY_FILTER_POINTS);
                        apply_distrib_filter($data['waiting'], HOURLY_FUZZY_FILTER_POINTS);
                        apply_distrib_filter($data['running'], HOURLY_FUZZY_FILTER_POINTS);
                }
        }
        if ($mode == "daily") {
                if (DAILY_FILTER_TYPE == FILTER_TYPE_PARABLE) {
                        apply_parable_filter($data['submit'], DAILY_FUZZY_FILTER_POINTS);
                        apply_parable_filter($data['waiting'], DAILY_FUZZY_FILTER_POINTS);
                        apply_parable_filter($data['running'], DAILY_FUZZY_FILTER_POINTS);
                } else if (DAILY_FILTER_TYPE == FILTER_TYPE_DISTRIB) {
                        apply_distrib_filter($data['submit'], HOURLY_FUZZY_FILTER_POINTS);
                        apply_distrib_filter($data['waiting'], HOURLY_FUZZY_FILTER_POINTS);
                        apply_distrib_filter($data['running'], HOURLY_FUZZY_FILTER_POINTS);
                }
        }

        $graph = new Graph(550, 250);
        $graph->SetMargin(40, 140, 30, 50);
        $graph->SetFrame(true, JPGRAPH_FRAME_FOREGROUND_COLOR, JPGRAPH_FRAME_BORDER_WIDTH);
        $graph->SetColor(JPGRAPH_GRAPH_BACKGROUND_COLOR);
        $graph->SetMarginColor(JPGRAPH_FRAME_BACKGROUND_COLOR);
        $graph->legend->Pos(0.03, 0.1);
        $graph->legend->SetFillColor(JPGRAPH_LEGEND_BACKGROUND_COLOR);
        $graph->legend->SetColor(JPGRAPH_LEGEND_FOREGROUND_COLOR, JPGRAPH_LEGEND_BORDER_COLOR);
        $graph->footer->right->Set(sprintf("Generated: %s", strftime("%Y-%m-%d")));
        $graph->footer->right->SetColor(JPGRAPH_NOTES_FOREGROUND_COLOR);

        //
        // Setup graph title ands fonts
        //		
        $graph->title->Set($title);
        $graph->title->SetColor(JPGRAPH_TITLE_MAIN_COLOR);
        $graph->title->SetFont(FF_FONT2, FS_BOLD);

        // 
        // Get smallest and largest key in data array. The keys are timestamps.
        // 
        $min = min(array_keys($data['submit']));
        $max = max(array_keys($data['submit']));

        // 
        // Sort data to ensure the timestamps are in order.
        // 
        foreach (array_keys($data) as $key) {
                if (!ksort($data[$key], SORT_NUMERIC)) {
                        die(sprintf("%s: failed sort data array for key %s in graph_draw_system_load", $options->prog, $key));
                        if ($options->debug) {
                                printf("debug: sorting data array keys (timestamps) for %s\n", $key);
                        }
                }
        }

        // 
        // Set the scale to match keys in data.
        // 
        $graph->SetScale("textint", 0, 0, $min, $max);
        $graph->SetY2Scale("lin");

        // 
        // Add some grace to the top so that the scale doesn't
        // end exactly at the max value. 
        // 
        $graph->yaxis->scale->SetGrace(20);

        // 
        // Customize to match mode:
        // 
        switch ($mode) {
                case "daily":
                        $graph->xaxis->SetLabelFormatCallback('timestamp_for_daily');
                        $graph->xaxis->scale->ticks->Set(2 * 3600, 3600);
                        $datefmt = "Y-m-d H:i";
                        break;
                case "monthly":
                        $graph->xaxis->SetLabelFormatCallback('timestamp_for_monthly');
                        $graph->xaxis->scale->ticks->Set(24 * 3600, 24 * 3600);
                        $datefmt = "Y-m-d";
                        break;
                case "yearly":
                        $graph->xaxis->SetLabelFormatCallback('timestamp_for_yearly');
                        $graph->xaxis->scale->ticks->Set(31 * 24 * 3600, 7 * 24 * 3600);
                        $datefmt = "Y-m-d";
                        break;
                case "weekly":
                        $graph->xaxis->SetLabelFormatCallback('timestamp_for_weekly');
                        $graph->xaxis->scale->ticks->Set(24, 3600);
                        break;
                case "hourly":
                        $graph->xaxis->SetLabelFormatCallback('timestamp_for_hourly');
                        $graph->xaxis->scale->ticks->Set(HOURLY_FUZZY_FILTER_POINTS, HOURLY_FUZZY_FILTER_POINTS / 4);
                        break;
                case "total":
                        $graph->xaxis->SetLabelFormatCallback('timestamp_for_total');
                        $graph->xaxis->scale->ticks->Set(24 * 3600, 24 * 3600);
                        $graph->xaxis->scale->ticks->SupressTickMarks(true);
                        $datefmt = "Y-m-d H:i";
                        break;
        }

        if ($options->debug) {
                if ($mode != "weekly" && $mode != "hourly") {
                        printf("debug: plotting %s system load data between %s (%d) and %s (%d)\n", $mode, date($datefmt, $min), $min, date($datefmt, $max), $max);
                } else {
                        printf("debug: plotting %s system load data between hour %d and %d\n", $mode, $min, $mode == "weekly" ? $max / 24 : $max);
                }
                $str = sprintf("debug: maximums for %s array: ", $mode);
                foreach (array_keys($data) as $key) {
                        $str .= sprintf("%s=%d ", $key, max($data[$key]));
                }
                printf("%s\n", $str);
        }

        // 
        // Setup the X-axis:
        // 
        $graph->xaxis->SetColor(JPGRAPH_XAXIS_SCALE_COLOR, JPGRAPH_XAXIS_LABEL_COLOR);
        $graph->xaxis->SetLabelAngle(90);
        $graph->xaxis->title->SetFont(FF_FONT1, FS_NORMAL);
        $graph->xaxis->title->SetColor(JPGRAPH_NOTES_FOREGROUND_COLOR);
        $graph->xaxis->SetFont(FF_FONT1, FS_BOLD);

        // 
        // Setup the Y-axis:
        // 
        $graph->yaxis->SetColor(JPGRAPH_YAXIS_SCALE_COLOR, JPGRAPH_YAXIS_LABEL_COLOR);
        $graph->y2axis->SetColor(JPGRAPH_YAXIS_SCALE_COLOR, JPGRAPH_YAXIS_LABEL_COLOR);
        $graph->ygrid->SetColor(JPGRAPH_GRAPH_FOREGROUND_COLOR);
        $graph->yaxis->title->Set("Running / Waiting (seconds)");
        $graph->y2axis->title->Set("Submitted jobs");

        // 
        // Create the line plots:
        // 
        $submit = new LinePlot(array_values($data['submit']), array_keys($data['submit']));
        $submit->SetColor('blue');
        $submit->SetLegend('Submit');
        $graph->AddY2($submit);

        $waiting = new LinePlot(array_values($data['waiting']), array_keys($data['waiting']));
        $waiting->SetColor('red');
        $waiting->SetLegend('Waiting');
        $graph->Add($waiting);

        $running = new LinePlot(array_values($data['running']), array_keys($data['running']));
        $running->SetColor('green');
        $running->SetLegend('Running');
        $graph->Add($running);

        //
        // Finally stroke the graph:
        //		
        $graph->Stroke($image);
}

// 
// Plot by daily system load.
// 
function graph_system_load_daily($graphdir, $options, $timestamp, $data)
{
        $image = sprintf("%s/sysload.png", $graphdir);
        $title = sprintf("System load (%s)", strftime("%Y-%m-%d", $timestamp));

        if ($options->debug) {
                printf("debug: creating graphic file %s\n", $image);
        }
        graph_draw_system_load($data, $image, $title, "daily", $options);
}

// 
// Plot by monthly system load.
// 
function graph_system_load_monthly($graphdir, $options, $timestamp, $data)
{
        $image = sprintf("%s/sysload.png", $graphdir);
        $title = sprintf("System load (%s)", strftime("%B %Y", $timestamp));

        if ($options->debug) {
                printf("debug: creating graphic file %s\n", $image);
        }
        graph_draw_system_load($data, $image, $title, "monthly", $options);
}

// 
// Plot by yearly system load.
// 
function graph_system_load_yearly($graphdir, $options, $timestamp, $data)
{
        $image = sprintf("%s/sysload.png", $graphdir);
        $title = sprintf("System load (year %s)", strftime("%Y", $timestamp));
        if ($options->debug) {
                printf("debug: creating graphic file %s\n", $image);
        }
        graph_draw_system_load($data, $image, $title, "yearly", $options);
}

// 
// Plot data accumulated by day of the week.
// 
function graph_system_load_weekly($graphdir, $options, $data)
{
        $image = sprintf("%s/sysload_weekly.png", $graphdir);
        $title = "System load total (by day of week)";
        if ($options->debug) {
                printf("debug: creating graphic file %s\n", $image);
        }
        graph_draw_system_load($data, $image, $title, "weekly", $options);
}

// 
// Plot data accumulated by hour of the day.
// 
function graph_system_load_hourly($graphdir, $options, $data)
{
        $image = sprintf("%s/sysload_hourly.png", $graphdir);
        $title = "System load total (by hours of the day)";
        if ($options->debug) {
                printf("debug: creating graphic file %s\n", $image);
        }
        graph_draw_system_load($data, $image, $title, "hourly", $options);
}

// 
// Plot over all data for the system lifetime.
// 
function graph_system_load_total($graphdir, $options, $data)
{
        $image = sprintf("%s/sysload_total.png", $graphdir);
        $title = "System load total (overall system lifetime)";
        if ($options->debug) {
                printf("debug: creating graphic file %s\n", $image);
        }
        graph_draw_system_load($data, $image, $title, "total", $options);
}

// 
// Helper function for merging data from the source array into the dest array.
// 
function system_load_merge_array(&$dest, &$source)
{
        foreach (array_keys($source) as $key) {
                $dest[$key] += $source[$key];
        }
}

// 
// Merge daily array with monthly array. Do a simple copy.
// 
function system_load_merge_daily_array(&$monthly, &$daily)
{
        system_load_merge_array($monthly, $daily);
}

// 
// The monthly array contains too many sample points (~ 24h * 31d). We need
// to reduce it into each day of the year.
// 
function system_load_merge_monthly_array(&$yearly, &$monthly)
{
        foreach ($monthly as $key => $data) {
                foreach ($data as $stamp => $value) {
                        if ($value) {       // Skip 0 values.
                                $date = getdate($stamp);
                                $time = mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);
                                $yearly[$key][$time] = $value;
                        }
                }
        }
}

// 
// Merge the yearly array with the total array. The number of keys in
// yearly should be ~365. Do a simple copy.
// 
function system_load_merge_yearly_array(&$total, &$yearly)
{
        system_load_merge_array($total, $yearly);
}

// 
// This function creates an over all view of the system load. What we are
// interested in is to plot number of submits, waiting and running time as
// a function of time. 
// 
// Three special graphs are:
//   * total load over lifetime
//   * system load per hour of day
//   * system load per weekday.
// 
// In addition we create per year, per month and per day graphs.
// 
function graph_system_load($graphdir, $data, $options)
{
        $weekly = array();     // by weekday system load
        $hourly = array();     // by hour system load
        $total = array();     // from birth to last sample system load
        // 
        // Initilize weekly, hourly and total arrays:
        // 
        foreach (array("submit", "waiting", "running") as $key) {
                for ($i = 0; $i < 7; ++$i) {
                        for ($j = 0; $j < 24; ++$j) {
                                $weekly[$key][$j + $i * 24] = 0;
                        }
                }
                for ($i = 0; $i < 24; ++$i) {
                        $hourly[$key][$i] = 0;
                }
                $total[$key] = array();
        }

        // 
        // Begin data mining from input data:
        // 
        foreach ($data as $year => $data1) {
                if (is_numeric($year)) {
                        $yearly = array();
                        $leapyear = date('L', mktime(0, 0, 0, 1, 1, $year));
                        // 
                        // Initilize the yearly array:
                        // 
                        foreach (array("submit", "waiting", "running") as $key) {
                                $yearly[$key] = array();
                                $days = 365 + $leapyear;       // Adjust for leap years
                                for ($i = 1; $i <= $days; ++$i) {
                                        $stamp = mktime(0, 0, 0, 1, $i, $year);
                                        $yearly[$key][$stamp] = 0;
                                }
                        }
                        foreach ($data1 as $month => $data2) {
                                if (is_numeric($month)) {
                                        $monthly = array();
                                        // 
                                        // Initilize monthly array:
                                        // 
                                        $days = date('t', mktime(0, 0, 0, $month, 1, $year));
                                        foreach (array("submit", "waiting", "running") as $key) {
                                                for ($i = 1; $i <= $days; ++$i) {
                                                        $stamp = mktime(0, 0, 0, $month, $i, $year);
                                                        $monthly[$key][$stamp] = 0;
                                                }
                                        }
                                        foreach ($data2 as $day => $data3) {
                                                if (is_numeric($day)) {
                                                        $daily = array();
                                                        // 
                                                        // Initilize daily array:
                                                        // 
                                                        foreach (array("submit", "waiting", "running") as $key) {
                                                                for ($i = 0; $i <= 24; ++$i) {
                                                                        $stamp = mktime($i, 0, 0, $month, $day, $year);
                                                                        $daily[$key][$stamp] = 0;
                                                                }
                                                        }
                                                        foreach ($data3 as $hour => $data4) {
                                                                if (is_numeric($hour)) {
                                                                        $hour = intval($hour);
                                                                        $stamp = mktime($hour, 0, 0, $month, $day, $year);
                                                                        $weekday = (date('w', $stamp) + 6) % 7;
                                                                        if ($options->debug) {
                                                                                printf("debug: adding %s (weekday %d, offset %d) to daily, weekly and hourly arrays (weekday 0 = monday)\n", date('Y-m-d H:i', $stamp), $weekday, $hour + $weekday * 24);
                                                                        }
                                                                        if (isset($data4['proctime'])) {
                                                                                foreach (array("waiting", "running") as $key) {
                                                                                        $daily[$key][$stamp] = $data4['proctime'][$key];
                                                                                        $weekly[$key][$hour + $weekday * 24] += $data4['proctime'][$key];
                                                                                        $hourly[$key][$hour] += $data4['proctime'][$key];
                                                                                }
                                                                        }
                                                                        $daily['submit'][$stamp] = $data4['submit']['count'];
                                                                        $weekly['submit'][$hour + $weekday * 24] += $data4['submit']['count'];
                                                                        $hourly['submit'][$hour] += $data4['submit']['count'];
                                                                }
                                                        }
                                                        // 
                                                        // Plot by day system load.
                                                        // 
                                                        graph_system_load_daily(sprintf("%s/%s/%s/%s", $graphdir, $year, $month, $day), $options, mktime(0, 0, 0, $month, $day, $year), $daily);
                                                        // 
                                                        // Merge daily array with monthly array.
                                                        // 
                                                        if ($options->debug) {
                                                                printf("debug: merging the daily array with the monthly\n");
                                                        }
                                                        system_load_merge_daily_array($monthly, $daily);
                                                }
                                        }
                                        // 
                                        // Plot by month system load.
                                        // 
                                        graph_system_load_monthly(sprintf("%s/%s/%s", $graphdir, $year, $month), $options, mktime(0, 0, 0, $month, 1, $year), $monthly);
                                        // 
                                        // Merge monthly array with yearly array.
                                        // 
                                        if ($options->debug) {
                                                printf("debug: merging the monthly array with the yearly array\n");
                                        }
                                        system_load_merge_monthly_array($yearly, $monthly);
                                }
                        }
                        // 
                        // Plot by year system load.
                        // 
                        graph_system_load_yearly(sprintf("%s/%s", $graphdir, $year), $options, mktime(0, 0, 0, 1, 1, $year), $yearly);
                        // 
                        // Merge yearly array with total array.
                        // 
                        if ($options->debug) {
                                printf("debug: merging the yearly array with the total array\n");
                        }
                        system_load_merge_yearly_array($total, $yearly);
                }
        }

        // 
        // Plot the three special graphs:
        // 
        graph_system_load_weekly($graphdir, $options, $weekly);
        graph_system_load_hourly($graphdir, $options, $hourly);
        graph_system_load_total($graphdir, $options, $total);
}

// 
// Generate graphics from collected statistics.
// 
function collect_flush_graphics($statdir, $data, $options)
{
        if (!USE_JPGRAPH_LIB) {
                if ($options->debug) {
                        printf("debug: not generating graphics from statistics (disabled in config.inc)\n");
                        return;
                }
        }

        foreach ($data as $hostid => $data1) {              // total level
                $graphdir = sprintf("%s/%s", $statdir, $hostid);
                graph_total_submit($graphdir, $hostid, $options, $data1);
                graph_total_proctime($graphdir, $hostid, $options, $data1);
                graph_total_state($graphdir, $hostid, $options, $data1);
                if (function_exists("graph_data_hook")) {
                        graph_data_hook($graphdir, $hostid, $options, 0, $data1, "total");
                }

                foreach ($data1 as $sect1 => $data2) {          // year level
                        if (!is_numeric($sect1)) {
                                continue;
                        }

                        $graphdir = sprintf("%s/%s/%s", $statdir, $hostid, $sect1);
                        $datetime = mktime(0, 0, 0, 1, 1, $sect1);
                        if ($options->debug) {
                                printf("debug: generate yearly (%s) graphics:\n", strftime("%Y-%m-%d", $datetime));
                                printf("debug:   hostid = %s\n", $hostid);
                                printf("debug:   resdir = %s\n", $graphdir);
                        }
                        graph_yearly_submit($graphdir, $hostid, $options, $datetime, $data2);
                        graph_yearly_proctime($graphdir, $hostid, $options, $datetime, $data2);
                        if (function_exists("graph_data_hook")) {
                                graph_data_hook($graphdir, $hostid, $options, $datetime, $data2, "yearly");
                        }

                        foreach ($data2 as $sect2 => $data3) {      // month level
                                if (!is_numeric($sect2)) {
                                        continue;
                                }

                                $graphdir = sprintf("%s/%s/%s/%s", $statdir, $hostid, $sect1, $sect2);
                                $datetime = mktime(0, 0, 0, $sect2, 1, $sect1);
                                if ($options->debug) {
                                        printf("debug: generate monthly (%s) graphics:\n", strftime("%Y-%m-%d", $datetime));
                                        printf("debug:   hostid = %s\n", $hostid);
                                        printf("debug:   resdir = %s\n", $graphdir);
                                }
                                graph_monthly_submit($graphdir, $hostid, $options, $datetime, $data3);
                                graph_monthly_proctime($graphdir, $hostid, $options, $datetime, $data3);
                                if (function_exists("graph_data_hook")) {
                                        graph_data_hook($graphdir, $hostid, $options, $datetime, $data3, "monthly");
                                }

                                foreach ($data3 as $sect3 => $data4) {  // day level
                                        if (!is_numeric($sect3)) {
                                                continue;
                                        }

                                        $graphdir = sprintf("%s/%s/%s/%s/%s", $statdir, $hostid, $sect1, $sect2, $sect3);
                                        $datetime = mktime(0, 0, 0, $sect2, $sect3, $sect1);
                                        if ($options->debug) {
                                                printf("debug: generate daily (%s) graphics:\n", strftime("%Y-%m-%d", $datetime));
                                                printf("debug:   hostid = %s\n", $hostid);
                                                printf("debug:   resdir = %s\n", $graphdir);
                                        }
                                        graph_daily_submit($graphdir, $hostid, $options, $datetime, $data4);
                                        graph_daily_proctime($graphdir, $hostid, $options, $datetime, $data4);
                                        if (function_exists("graph_data_hook")) {
                                                graph_data_hook($graphdir, $hostid, $options, $datetime, $data4, "daily");
                                        }
                                }
                        }
                }
        }

        // 
        // Generate system load graph (this function creates huge arrays). 
        // 
        graph_system_load(sprintf("%s/all", $statdir), $data['all'], $options);
}

// 
// Collect statistics from job directories.
// 
function collect_statistics($jobsdir, $statdir, $options)
{
        $statfile = sprintf("%s/cache.ser", $statdir);    // collected data cached from previous runs

        $queued = null;
        if ($options->qmode == LIST_QUEUE_ONCE) {
                $queued = array();
                get_queued_jobs($queued);
        }

        if (!is_dir($jobsdir) && !is_link($jobsdir)) {
                die(sprintf("%s: path %s is not a directory\n", $options->prog, $jobsdir));
        }

        // 
        // Create statistics directories if missing:
        // 
        if (!file_exists($statdir)) {
                if ($options->debug) {
                        printf("debug: creating statistics directory '%s'\n", $statdir);
                }
                if (!mkdir($statdir)) {
                        die(sprintf("%s: failed create directory '%s'\n", $options->prog, $statdir));
                }
        }

        // 
        // Load statistics data from previous runs:
        // 
        $statdata = array();
        if (!file_exists($statfile)) {
                $options->force = true;
        }
        if (!$options->force) {
                if (file_exists($statfile)) {
                        if ($options->debug) {
                                printf("debug: reading collected data from serialized cache (%s)\n", $statfile);
                        }
                        $statdata = unserialize(file_get_contents($statfile));
                }
        }

        // 
        // Repair statistics(?):
        // 
        if ($options->repair) {
                collect_read_data($statdir, $statdata, $options);
                collect_recalc_data($statdata, $options);
        }

        // 
        // Collect statistics:
        // 
        $handle = @opendir($jobsdir);
        if ($handle) {
                if ($options->debug) {
                        printf("debug: processing host ID directories under %s\n", $jobsdir);
                }
                while (($file = readdir($handle)) !== false) {
                        $path = sprintf("%s/%s", $jobsdir, $file);
                        if ($file == "." || $file == "..") {
                                continue;
                        }
                        if (!is_dir($path)) {
                                if ($options->debug) {
                                        printf("debug: skipping non-directory %s\n", $path);
                                }
                                continue;
                        } else {
                                if ($options->debug) {
                                        printf("debug: processing host ID directory %s\n", $file);
                                }
                                collect_hostid_data($file, $statdir, $options, $statdata, $queued);
                        }
                }
                closedir($handle);
        } else {
                die(sprintf("%s: failed reading directory %s\n", $options->prog, $jobsdir));
        }

        // 
        // Discard crashed job data (mapped to year 1970).
        // 
        if (isset($statdata['1970'])) {
                unset($statdata['1970']);
        }

        // 
        // Save statistics for next run:
        // 
        if ($options->debug) {
                printf("debug: writing collected data to serialized cache (%s)\n", $statfile);
                if ($options->verbose) {
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
function main(&$argc, $argv)
{
        $prog = basename(array_shift($argv));
        $vers = trim(file_get_contents("../VERSION"));

        // 
        // Setup defaults in options array:
        // 
        $options = array(
                "force"   => false,
                "qmode"   => LIST_QUEUE_ONCE,
                "repair"  => false,
                "debug"   => false,
                "verbose" => 0,
                "prog"    => $prog,
                "version" => $vers
        );

        // 
        // Fill $options with command line options.
        // 
        $options = (object) $options;
        parse_options($argc, $argv, $options);

        // 
        // Dump options:
        //
    if ($options->debug) {
                var_dump($options);
        }

        // 
        // Begin collect statistics.
        // 
        collect_statistics(sprintf("%s/jobs", CACHE_DIRECTORY), sprintf("%s/stat", CACHE_DIRECTORY), $options);
}

// 
// Start normal script execution.
// 
main($_SERVER['argc'], $_SERVER['argv']);
?>
