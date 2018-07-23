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
// This file demonstrate how to properly handle error and warning conditions
// inside pre_enqueue_hook().
// 
// 
// Calling put_error() and return false will make the script show the error
// message and exit. 
// 
// To inform the user about potential problems where it's still OK to continue
// enqeue the job, call put_warning() and  return true. If put_warning() is 
// called with $jobdir as an parameter, then the warning message is appended 
// to an file named warning in the job directory.
// 
// Only the message in last call to put_error() or put_warning() will be used
// as the message showed to the end user.
// 
function pre_enqueue_hook(&$file, $jobdir, &$handled, &$jobs)
{
        // 
        // Just some variables that let us test reporting 
        // error and warning conditions.
        // 
        $cond1 = true;     // error
        $cond2 = true;     // warning
        $multi = true;     // simulate enqueue multiple jobs.

        if ($cond1) {
                put_error("An critical error occured.");
                return false;
        }

        if ($cond2) {
                // 
                // This warning message will be saved in the job directory:
                // 
                put_warning("Job was enqueued with warning.", $jobdir);
        }

        // 
        // If enqueue multiple jobs from a single submit, supply a general 
        // warning message to the end user.
        // 
        if ($multi && has_warnings()) {
                // 
                // Don't supply an jobdir argument here!
                // 
                put_warning("One or more jobs where enqueued with warnings.");
        }

        return true;
}
