<?php

// -------------------------------------------------------------------------------
//  Copyright (C) 2007-2008 Anders Lövgren
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
// This script adds job control functionality. Note that simple or advanced 
// job control must be defined in conf/config.inc.
// 

//
// Get configuration.
// 
include "../conf/config.inc";
include "../include/common.inc";
include "../include/delete.inc";

// 
// The error handler.
// 
function error_handler($type, $reason = null)
{
    // 
    // Redirect caller back to queue.php and let it report an error.
    // 
    if(isset($reason)) {
	header("Location: queue.php?error=jobcontrol&type=$type&reason=$reason");
    } else {
	header("Location: queue.php?error=jobcontrol&type=$type");
    }
}

// 
// Check required parameters.
// 
if(!isset($_REQUEST['jobid']) || !isset($_REQUEST['result'])) {
    error_handler("params");
}
if(!isset($_COOKIE['hostid'])) {
    error_handler("hostid");
}
if(!isset($_REQUEST['signal'])) {
    error_handler("pid", "type");
} else {
    if(!in_array($_REQUEST['signal'], array_keys($signals))) {
	error_handler("pid", "type");
    }
}

// 
// Get request parameters.
// 
$jobid  = $_REQUEST['jobid'];    // Job ID
$resdir = $_REQUEST['result'];   // Job directory.

// 
// Get hostid from cookie.
// 
$hostid = $_COOKIE['hostid'];

// 
// Build path to result directory:
// 
$resdir = sprintf("%s/jobs/%s/%s", CACHE_DIRECTORY, $hostid, $resdir);

// 
// If result directory is missing, the show an error message.
// 
if(!file_exists($resdir)) {
    error_handler("resdir");
}

// 
// Check that pid-file exists:
// 
$pidfile = sprintf("%s/pid", $resdir);
if(!file_exists($pidfile)) {
    error_handler("pid", "file");
}

$signal = $signals[$_REQUEST['signal']]['value'];
$action = $signals[$_REQUEST['signal']]['action'];

// 
// Try send signal to process:
// 
$pid = intval(file_get_contents($pidfile));
if(!posix_kill($pid, 0)) {
    // 
    // No process running or signaling not permitted.
    // 
    error_handler("pid", "file");
} else {
    if(!posix_kill($pid, $signal)) {
	// 
	// Failed send signal to process.
	// 
	error_handler("pid", "perm");
    }
}

// 
// Perform additional tasks associated with sending the signal:
// 
switch($action) {    
 case "clear":
    delete_single_job($_COOKIE['hostid'], $_REQUEST['result'], $_REQUEST['jobid']);
    break;
 case "flag":
    file_put_contents(sprintf("%s/signal", $resdir), $_REQUEST['signal']);
    break;
 case "none":
 default:
    break;
}

// 
// Used for proper redirect back.
// 
$sort   = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : "none";
$filter = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : "all";

header(sprintf("Location: queue.php?show=queue&sort=%s&filter=%s", $sort, $filter));

?>
