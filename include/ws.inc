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
// This is the web service support functions that gets called from scripts 
// under ws/http (lightweight) or ws/xmlrpm (apache org spec).
// 
// All functions should return false on error and set the error 
// message by calling put_error().
// 

// 
// Suspend (pause) the job. Requires job control to be enabled in the
// system configuration (conf/config.inc).
// 
function suspend($resdir, $jobid)
{
    put_error("Not yet implemented");
    return false;
}

// 
// Resume (continue) the job. Requires job control to be enabled in the
// system configuration (conf/config.inc).
// 
function resume($resdir, $jobid)
{
    put_error("Not yet implemented");
    return false;
}

// 
// Enqueue (starts) a new job. If indata is null, then its assumed that
// indata comes from an uploaded file (set in $_FILES). The $job variable
// gets filled with data about the newly enqueued job.
// 
function enqueue(&$indata, &$job)
{
    if(!enqueue_job($indata, $job)) {
	return false;
    }
    return true;
}

// 
// Dequeue (stop and removes) a job.
// 
function dequeue($resdir, $jobid)
{
    if(!delete_single_job($hostid, $resdir, $jobid)) {
	return false;
    }
    return true;
}

// 
// List queued jobs. The $sort and $filter argument can be used to
// limit what jobs gets returned.
// 
function queue(&$jobs, $sort = "none", $filter = "all")
{
    $jobs = get_jobs($sort, $filter);
    if($jobs == false) {
	return false;
    }
    return true;
}

// 
// Provides access to the result directory. If $jobid is null, then a list
// of all job directories is returned. If $jobid is not null, then all files
// from that job directory is returned. If $file is set, then $result is
// set to the absolute path of the file (this allowes caller e.g. to use the 
// fast readfile() for pipelining the file content).
// 
// The result is returned in the $result parameter.
// 
// result(null, null, null, $result)        : get all job directories
// result($resdir, $jobid, null, $result)   : get content of this job directory
// result($resdir, $jobid, $file, $result)  : absolute path of file.
// 
function result($resdir, $jobid, $file, &$result)
{
    return true;
}

?>
