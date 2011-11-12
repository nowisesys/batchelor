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
// Publish job results.
// 

define("PUBLISH_INDEX_FILE", "index.ser");
define("PUBLISH_STAMP_FILE", "published");

// 
// Add this job to the published jobs.
// 
function publish_add($jobdir, $title, $descr)
{
        $published = array();

        $path = sprintf("%s/publish", CACHE_DIRECTORY);
        if (!file_exists($path)) {
                if (!mkdir($path)) {
                        put_error("Failed create publish directory");
                        return false;
                }
        }

        // 
        // Create the destination directory for publish information directory.
        // 
        while (true) {
                $dest = sprintf("%s/%s", $path, md5(microtime()));
                if (mkdir($dest)) {
                        break;
                }
        }

        // 
        // Link jobdir inside the destination directory:
        // 
        if (!symlink($jobdir, sprintf("%s/jobdir", $dest))) {
                put_error("Failed link job directory");
                return false;
        }

        // 
        // Write description and title files:
        // 
        file_put_contents(sprintf("%s/descr", $dest), $descr);
        file_put_contents(sprintf("%s/title", $dest), $title);

        // 
        // Add published file inside job directory:
        // 
        file_put_contents(sprintf("%s/%s", $jobdir, PUBLISH_STAMP_FILE), time());

        // 
        // Load array of published jobs:
        // 
        $path = sprintf("%s/publish/%s", CACHE_DIRECTORY, PUBLISH_INDEX_FILE);
        if (file_exists($path)) {
                $published = unserialize(file_get_contents($path));
        }

        // 
        // Append to array of published jobs:
        // 
        $published[basename($dest)] = $title;

        // 
        // Save array of published jobs:
        // 
        file_put_contents($path, serialize($published));

        // 
        // Force regeneration of queue.ser:
        // 
        unlink(sprintf("%s/queue.ser", dirname($jobdir)));

        // 
        // Return directory name:
        // 
        return basename($dest);
}

// 
// Edit data for an already published job.
// 
function publish_edit($name, $title, $descr)
{
        $published = array();

        $path = sprintf("%s/publish", CACHE_DIRECTORY);
        if (!file_exists($path)) {
                if (!mkdir($path)) {
                        put_error("Failed create publish directory");
                        return false;
                }
        }
        $dest = sprintf("%s/%s", $path, $name);

        // 
        // Load array of published jobs:
        // 
        $path = sprintf("%s/publish/%s", CACHE_DIRECTORY, PUBLISH_INDEX_FILE);
        if (file_exists($path)) {
                $published = unserialize(file_get_contents($path));
        }

        // 
        // Write description and title files:
        // 
        file_put_contents(sprintf("%s/descr", $dest), $descr);
        file_put_contents(sprintf("%s/title", $dest), $title);

        // 
        // Append to array of published jobs:
        // 
        $published[basename($name)] = $title;

        // 
        // Save array of published jobs:
        // 
        file_put_contents($path, serialize($published));

        return true;
}

// 
// Remove this job from the published jobs.
// 
function publish_remove($name)
{
        $published = array();

        // 
        // Load array of published jobs:
        // 
        $path = sprintf("%s/publish/%s", CACHE_DIRECTORY, PUBLISH_INDEX_FILE);
        if (file_exists($path)) {
                $published = unserialize(file_get_contents($path));
        }

        // 
        // Create path to publish directory:
        // 
        $dest = sprintf("%s/publish/%s", CACHE_DIRECTORY, $name);

        // 
        // Remove published file inside job directory:
        // 
        $jobdir = readlink(sprintf("%s/jobdir", $dest));
        unlink(sprintf("%s/%s", $jobdir, PUBLISH_STAMP_FILE));

        // 
        // Force regeneration of queue.ser:
        // 
        unlink(sprintf("%s/queue.ser", dirname($jobdir)));

        // 
        // Remove publish information directory:
        // 
        if (file_exists($dest)) {
                // 
                // Remove files inside publish directory:
                // 
                if (($handle = opendir($dest))) {
                        while ($file = readdir($handle)) {
                                if ($file == "." || $file == "..") {
                                        continue;
                                }
                                if (!unlink(sprintf("%s/%s", $dest, $file))) {
                                        put_error("Failed unlink publish data file");
                                        return false;
                                }
                        }
                        closedir($handle);
                }
                // 
                // Remove the directory itself:
                // 
                if (!rmdir($dest)) {
                        put_error("Failed remove publish directory");
                        return false;
                }
        }

        // 
        // Remove entry from index:
        // 
        unset($published[$name]);

        // 
        // Save array of published jobs:
        // 
        file_put_contents($path, serialize($published));
        return true;
}

// 
// Get directory path for this published job.
// 
function publish_get_path($name)
{
        $path = sprintf("%s/publish/%s", CACHE_DIRECTORY, $name);
        return $path;
}

// 
// Return name of publish directory having a link to jobdir.
// 
function publish_get_name($jobdir)
{
        $published = publish_get_data();

        foreach ($published as $name => $title) {
                $path = publish_get_path($name);
                $path = sprintf("%s/jobdir", $path);    // The symbolic link
                if (readlink(sprintf($path)) == $jobdir) {
                        return $name;
                }
        }

        return null;    // Not found
}

// 
// Get description for this published job.
// 
function publish_get_description($name)
{
        $path = sprintf("%s/publish/%s/descr", CACHE_DIRECTORY, $name);
        return file_get_contents($path);
}

// 
// Get title of published job.
// 
function publish_get_title($name)
{
        $path = sprintf("%s/publish/%s", CACHE_DIRECTORY, PUBLISH_INDEX_FILE);
        if (file_exists($path)) {
                $published = unserialize(file_get_contents($path));
                return $published[$name];
        } else {
                return $name;
        }
}

// 
// Get data for all published results.
// 
function publish_get_data()
{
        $published = array();

        // 
        // Load array of published jobs:
        // 
        $path = sprintf("%s/publish/%s", CACHE_DIRECTORY, PUBLISH_INDEX_FILE);
        if (!file_exists($path)) {
                publish_rebuild_index();
        }
        if (file_exists($path)) {
                $published = unserialize(file_get_contents($path));
        }

        return $published;
}

// 
// Rebuilds index.ser
// 
function publish_rebuild_index()
{
        $published = array();
        $path = sprintf("%s/publish", CACHE_DIRECTORY);

        if (($handle = opendir($path))) {
                while ($file = readdir($handle)) {
                        if ($file == "." || $file == "..") {
                                continue;
                        }
                        if (is_dir(sprintf("%s/%s", $path, $file))) {
                                $name = sprintf("%s/%s/title", $path, $file);
                                if (file_exists($name)) {
                                        $published[$file] = trim(file_get_contents($name));
                                }
                        }
                }
                closedir($handle);
        }

        $path = sprintf("%s/%s", $path, PUBLISH_INDEX_FILE);
        file_put_contents($path, serialize($published));
}

// 
// Return true if hostid (usually read from cookie) is owner of this published
// job. The owner might be more than one person if queue sharing is enabled.
// 
function publish_is_owner($hostid, $name)
{
        $link = sprintf("%s/publish/%s/jobdir", CACHE_DIRECTORY, $name);
        $path = readlink($link);
        
        // 
        // Extract hostid from path:
        // 
        $name = basename(dirname($path));
        return $name == $hostid ? true : false;
}

// 
// Send result zip-file for published job.
// 
function publish_send_result($name)
{
        $path = publish_get_path($name);
        if (!file_exists($path)) {
                put_error("Directory is missing for the published job");
                return false;
        }

        $file = sprintf("%s/jobdir/jobid", $path);
        if (!file_exists($file)) {
                put_error("The job ID is missing for the published job");
                return false;
        }
        $jobid = file_get_contents($file);

        $path = sprintf("%s/jobdir", $path);
        if (!file_exists($path)) {
                put_error("The result directory is missing for the published job");
                return false;
        }

        // 
        // Send the zip-file:
        // 
        download_result($path, $jobid);
        return true;
}

function publish_send_indata($name)
{
        $path = publish_get_path($name);
        if (!file_exists($path)) {
                put_error("Directory is missing for the published job");
                return false;
        }

        $path = sprintf("%s/jobdir", $path);
        if (!file_exists($path)) {
                put_error("The result directory is missing for the published job");
                return false;
        }

        // 
        // Send indata:
        // 
        download_indata($path);
        return true;
}

?>
