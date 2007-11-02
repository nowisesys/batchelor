<?php

// 
// The minimum file size accepted (in bytes).
// 
define ("MIN_FILE_SIZE", 10000);

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

?>
