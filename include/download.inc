<?php

// -------------------------------------------------------------------------------
//  Copyright (C) 2011 Anders Lövgren
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
// Support functions for downloading result and indata. These functions is
// used both by the download function accessable from the queue and by the
// publish function (if enabled by config).
// 

// 
// Send result in zip-file.
// 
function download_result($resdir, $jobid)
{
        // 
        // Now create the result zip if missing.
        //
        $zipdir = "result";
        $zipfile = "result.zip";

        chdir($resdir);
        if (!file_exists("result.zip")) {
                // 
                // Use bundled PECL zip extention if available.
                //
                if (extension_loaded("zip") && version_compare(phpversion(), "5.2.0", ">=")) {
                        // 
                        // This is a workaround because the PHP4 compiler will die
                        // on enums (like ZipArchive::CREATE).
                        // 
                        $zipinc = realpath(sprintf("%s/../include/zip5.inc", dirname(__FILE__)));
                        include $zipinc;
                        if (!create_zipfile($zipfile, $zipdir)) {
                                error_handler("zip");
                        }
                } else {
                        // 
                        // Fallback on external command.
                        //
                        $handle = popen(sprintf(ZIP_FILE_COMMAND, $zipfile, $zipdir), "r");
                        pclose($handle);
                }
        }

        // 
        // Make sure the archive where created:
        //
        if (file_exists($zipfile)) {
                //
                // Hint browser about filename to use for "save as..."
                // 
                header(sprintf("Content-Disposition: attachment; filename=\"%s\"", sprintf("result-job-%s.zip", $jobid)));
                header(sprintf("Content-Type: %s", "application/zip"));
                header(sprintf("Content-Length: %d", filesize($zipfile)));

                // 
                // Now send the file:
                // 
                readfile($zipfile);
        } else {
                error_log("Failed create zip file, make sure the zip command is correct or that the zip extension is loaded.");
                error_handler("zip");
        }
}

// 
// Download indata.
// 
function download_indata($resdir)
{
        $indata = sprintf("%s/indata", $resdir);

        // 
        // Make sure that indata exists.
        // 
        if (file_exists($indata)) {
                //
                // Hint browser about filename to use for "save as..."
                // 
                header(sprintf("Content-Disposition: attachment; filename=\"%s\"", sprintf("indata.txt", $indata)));
                header(sprintf("Content-Type: %s", "text/plain"));
                header(sprintf("Content-Length: %d", filesize($indata)));

                // 
                // Now send the file:
                // 
                readfile($indata);
        }
}


