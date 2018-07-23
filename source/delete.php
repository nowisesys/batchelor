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
// Deletes a pending or finished job. Crashed and error job also
// counts as finished.
// 
//
// Get configuration.
// 
include "../conf/config.inc";
include "../include/queue.inc";
include "../include/common.inc";
include "../include/delete.inc";

// 
// The error handler.
// 
function error_handler($type)
{
        // 
        // Log any error messages to web server log.
        // 
        log_errors();

        // 
        // Redirect caller back to queue.php and let it report an error.
        // 
        header("Location: queue.php?error=delete&type=$type");
        exit(0);
}

// 
// Sanity check:
// 
if (!isset($_COOKIE['hostid'])) {
        error_handler("hostid");
}

// 
// Delete multiple jobs at once or a single job.
// 
if (isset($_REQUEST['multiple'])) {
        // 
        // Sanity check:
        // 
        if (!isset($_REQUEST['filter'])) {
                error_handler("params");
        }

        if (!delete_multiple_jobs($_COOKIE['hostid'], $_REQUEST['filter'])) {
                error_handler("delete");
        }
} else {
        // 
        // Sanity check:
        // 
        if (!isset($_REQUEST['jobid']) || !isset($_REQUEST['result'])) {
                error_handler("params");
        }

        if (!delete_single_job($_COOKIE['hostid'], $_REQUEST['result'], $_REQUEST['jobid'])) {
                error_handler("delete");
        }
}

// 
// Used for proper redirect back.
// 
$sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : "none";
$filter = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : "all";

header(sprintf("Location: queue.php?show=queue&sort=%s&filter=%s", $sort, $filter));

