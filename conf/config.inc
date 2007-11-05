<?php

// 
// Make sure timezone is UTC to prevent troubles with daylight saving time.
// 
if(ini_get("date.timezone") != "UTC") {
    ini_set("date.timezone", "UTC");
}

// 
// The batch cammand to run. The %s gets substituted with the batch
// job command, including its arguments.
// 
define ("BATCH_COMMAND", "echo \"%s\" | batch");

// 
// List all batch jobs. Jobs in queue 'b' is pending batch jobs and 
// jobs in queue '=' is currently running jobs.
// 
define ("BATCH_LISTING", "at -l");

// 
// Remove a pending job from the batch queue. The %d gets expanded to
// the job ID to remove from queue.
// 
define ("BATCH_REMOVE", "atrm %d");

// 
// 
// The minimum file size accepted (in bytes). Set to 0 to disable
// minimum file size check.
// 
define ("MIN_FILE_SIZE", 5000);

// 
// The number of seconds between reload of the page waiting for
// running jobs to finish (in seconds).
// 
define ("PAGE_REFRESH_RATE", 30);

// 
// Use session cookies or persistent cookies?
//
define ("USE_SESSION_COOKIES", false);

// 
// Cookie life time (not for session cookies).
// 
define ("COOKIE_LIFE_TIME", 3600 * 24 * 7);

// 
// Path to directory where job data gets saved. This directory must
// be read/write to the user the webserver is running as.
// 
define ("CACHE_DIRECTORY", realpath(dirname(__FILE__) . "/../cache"));
define ("CACHE_PERMISSION", 0750);

// 
// Should we save peer address <=> hostid associations to file?
// Useful for debbuging and for generating statistics.
// 
define ("SAVE_HOSTID_MAPPING", true);

// 
// Show delete job link.
// 
define ("SHOW_JOB_DELETE_LINK", true);

// 
// Format of UNIX timestamps, see manual page for date() function. 
// Localized output is enabled by setlocale() and using "%c" as
// format string.
// 
define ("TIMESTAMP_FORMAT", "%Y-%m-%d %H:%M:%S");

?>
