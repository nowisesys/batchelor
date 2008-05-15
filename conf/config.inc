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
// The timezone can either be set system wide (in php.ini) or application
// local (here in config.inc). Setting the timezone is recommended as it
// affects how date() and strftime() formats timestamps (UNIX timestamp).
// 
if(!ini_get("date.timezone")) {
    ini_set("date.timezone", "CET");
}

// 
// The contact address for problems running batchelor. This address is used
// in some error messages and is also printed in page footer. You should set
// it to a real address!
//
define ("CONTACT_NAME", "System Manager (ChemGPS at BMC)");
define ("CONTACT_ADDRESS", "chemgps@bmc.uu.se");
define ("CONTACT_STRING", sprintf("<a href=\"mailto:%s\">%s</a>", 
				  CONTACT_ADDRESS, CONTACT_NAME));

// 
// This command lists number of running jobs from all users.
// 
define ("BATCH_RUNNING", "at -l | grep '=' | wc -l");

// 
// The maximum number of jobs thats allowed to run simultanous. The number
// to use must be determined by experimenting. A good starting point might be
// to allow 2 * N(c) - 1, where N(c) == number of CPU cores. 
// 
// Use 0 to force all jobs to be queued using the BATCH_COMMAND command.
// 
define ("BATCH_LIMIT", 15);

// 
// The batch cammand to run. The %s gets substituted with the batch job
// command, including its arguments. The command BATCH_COMMAND should queue
// the job and the BATCH_EXECUTE should start the job immidiatly. The
// BATCH_EXECUTE is used iff BATCH_RUNNING < BATCH_LIMIT, otherwise the job
// is queued for later execution.
// 
define ("BATCH_COMMAND", "echo \"%s\" | batch");
define ("BATCH_EXECUTE", "echo \"%s\" | at now");

// 
// List all batch jobs. Jobs in queue 'b' is pending batch jobs and 
// jobs in queue '=' is currently running jobs.
// 
define ("BATCH_LISTING", "at -l");

// 
// Remove a pending job from the batch queue. The %d is substituted
// with the job ID to remove from queue.
// 
define ("BATCH_REMOVE", "atrm %d");

// 
// 
// The minimum/maximum file size accepted (in bytes). Set to 0 to 
// disable minimum/maximum file size check. The maximum is affected
// by PHP's upload_max_filesize.
// 
define ("UPLOAD_MIN_FILESIZE", 0);
define ("UPLOAD_MAX_FILESIZE", 1024 * 1024);

// 
// Should original name of uploaded file be preserved?
// 
define ("UPLOAD_PRESERVE_FILENAME", false);

// 
// The number of seconds between reload of the page waiting for
// running jobs to finish (in seconds). Set to 0 to disable automatic
// page refresh. Note that some browsers don't handle meta refresh
// correct, see http://en.wikipedia.org/wiki/Meta_refresh.
// 
define ("PAGE_REFRESH_RATE", 30);

// 
// Use session cookies or persistent cookies?
//
define ("USE_SESSION_COOKIES", false);

// 
// Cookie life time (not for session cookies).
// 
define ("COOKIE_LIFE_TIME", 3600 * 24 * 14);

// 
// Path to directory where job data gets saved. This directory must
// be read/write to the user the webserver is running as.
// 
define ("CACHE_DIRECTORY", "/var/cache/chemgps");
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

// 
// If PHP >= 5.2 and the PECL zip extension (http://pecl.php.net) is loaded,
// then we use it for creating zip-archives for download. If missing, then
// this command is used as fallback. 7-zip, licensed under GPL/BSD license,
// is free to use.
// 
// The first '%s' is zip-file to create, the second '%s' is the name of the
// directory to archive.
// 
// This example is for Info-ZIP (also free):
define ("ZIP_FILE_COMMAND", "zip -r %s %s");
// 
// This example is for 7-Zip:
// 
// define ("ZIP_FILE_COMMAND", "7z a %s %s -tzip");

// 
// Configures whether to present the job queue with icons or plain text 
// inside the table view.
// 
define ("USE_ICONIZED_QUEUE", true);

// 
// Configure the menu style. Use either "graphic", "text" or "compact".
// 
define ("MENU_STYLE", "graphic");

// 
// Configure the menu orientation. Use either "vertical" or "horizontal".
// 
define ("MENU_ORIENTATION", "vertical");

// 
// Use the JpGraph library for generating graphics from statistics? Enable
// this is recommended. JpGraph can be downloaded from http://www.aditus.nu/jpgraph/
// 
// Settings for JpGraph can be tuned inside the file conf/jpgraph.inc
// 
define ("USE_JPGRAPH_LIB", true);

// 
// Define how to expose the statistics module. Possible values are:
// * public => expose statistics for all.
// * hidden => hide link to statistics module.
// * locked => only trusted host can access statistics.
// 
define ("STATISTICS_EXPOSED", "public");

// 
// If STATISTICS_EXPOSED is locked, then set this to a colon (':') separated
// list of hosts that are allowed to access the statistics module.
// 
define ("STATISTICS_TRUSTED", "127.0.0.1");

// 
// Define footer info.
// 
define ("FOOTER_INFOTEXT", true);
define ("FOOTER_COOPERATIONLOGOS", true);
define ("FOOTER_SHOW_NOTES", true);
define ("FOOTER_SHOW_COPYING", false);

// 
// Define the string used in the title for each page.
// 
define ("HTML_PAGE_TITLE", "ChemGPS-NP");

?>
