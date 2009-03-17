<?php

// -------------------------------------------------------------------------------
//  Copyright (C) 2007 Anders L�vgren
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
// This is an example of enqueue multiple jobs from an single submit. A real
// world application should probably split indata using a regexp.
// 

// 
// Setting handled to true tells the caller that we are handling the
// enqueuing internal in this function. When calling enqueue_single_job(),
// we must pass index != 0 and increment it between each call.
// 
// Note that the call to enqueue_single_job() will modify jobdir to point 
// to the new sub job directory. We could use it when calling put_warning() 
// to write per sub job warning files.
// 
function pre_enqueue_hook(&$file, $jobdir, &$handled, &$jobs)
{
    $handled = true;              // We are handling enqueuing from here.
    $index = 1;                   // The index for each queued job.
    
    $fs = fopen($file, "r");
    if(!$fs) {
	put_error("Failed open $file");
	return false;
    }

    // 
    // Simply create one job for each line of indata. Name the indata from
    // the line content.
    // 
    while(($line = trim(fgets($fs)))) {
	if(strlen($line)) {
	    $job = null;
	    $out = sprintf("%s/%s", $jobdir, $line);
	    file_put_contents($out, $line);
	    if(!enqueue_single_job($out, $jobdir, $job, $index++)) {
		put_error(get_last_error());
		return false;
	    }
	    $jobs[] = $job;       // Append job to result array.
	}
    }
    
    fclose($fs);
    return true;
}

?>