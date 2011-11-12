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
// Helper function for deleting a directory (recursive).
// 
function delete_directory($root)
{
        if (file_exists($root)) {
                $handle = opendir($root);
                if ($handle) {
                        while (false !== ($file = readdir($handle))) {
                                if ($file != "." && $file != "..") {
                                        $path = sprintf("%s/%s", $root, $file);
                                        if (is_dir($path)) {
                                                delete_directory($path);
                                        }
                                        if (is_file($path) || is_link($path)) {
                                                unlink($path);
                                        }
                                }
                        }
                        closedir($handle);
                } else {
                        put_error("Failed open directory");
                        return false;
                }
                rmdir($root);
        }
        return true;
}

// 
// Delete job directory (unqueue) for a single job.
// 
function delete_single_job($hostid, $resdir, $jobid)
{
        //
        // Record all deleted job directories to the queue cache
        // will be properly updated.
        //
        $status = sprintf("%s/jobs/%s/status.log", CACHE_DIRECTORY, $hostid);

        // 
        // The resdir argument is actually the name of the jobdir.
        // 
        $jobdir = $resdir;

        // 
        // Refuse to delete published jobs.
        // 
        if (file_exists(sprintf("%s/published", $jobdir))) {
                put_error("This job is published and can't be deleted");
                return false;
        }

        // 
        // Build path to result directory:
        // 
        $resdir = sprintf("%s/jobs/%s/%s", CACHE_DIRECTORY, $hostid, $resdir);

        // 
        // If result directory is missing, the silently refuse to unqueue job.
        // 
        if (file_exists($resdir)) {
                $jobfile = sprintf("%s/jobid", $resdir);
                if (file_exists($jobfile)) {
                        if (file_get_contents($jobfile) != $jobid) {
                                put_error("Job ID don't match recorded Job ID");
                                return false;
                        }

                        // 
                        // We got an existing result directory with authentic job ID under
                        // the cache directory matching hostid. It should be safe to unqueue
                        // the job and delete the directory.
                        // 
                        $handle = popen(sprintf(BATCH_REMOVE, $jobid), "r");
                        pclose($handle);

                        // 
                        // Recursive delete the job directory.
                        // 
                        if (!delete_directory($resdir)) {
                                return false;
                        }
                } else if ($jobid == 0) {
                        // 
                        // This is a job directory crashed due to missconfiguration.
                        // 
                        if (!delete_directory($resdir)) {
                                return false;
                        }
                }
        } else {
                put_error("The job directory don't exists");
                return false;
        }

        // 
        // Append deleted job to status log:
        // 
        file_put_contents($status, sprintf("%s\n", $jobdir), FILE_APPEND);
        return true;
}

// 
// Delete a list of jobs.
// 
function delete_multiple_jobs($hostid, $filter)
{
        $jobs = get_jobs($hostid, "none", $filter);
        foreach ($jobs as $jobdir => $job) {
                if (isset($job['published'])) {
                        continue;       // Don't delete published jobs.
                }
                if ($job['state'] != "running") {
                        if (!delete_single_job($hostid, $jobdir, $job['jobid'])) {
                                return false;
                        }
                }
        }
        return true;
}

?>
