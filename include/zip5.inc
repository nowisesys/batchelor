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
// This file adds zip archive handling.
// 
// 
// This function will recursive add any file or directory in
// subdir to the zipfile represented by the objzip object.
// 
function add_zipfile(&$objzip, $subdir)
{
        $handle = opendir($subdir);
        if ($handle) {
                while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != "..") {
                                $path = sprintf("%s/%s", $subdir, $file);
                                if (is_dir($path)) {
                                        if (!add_zipfile($objzip, $path)) {
                                                return false;
                                        }
                                } else {
                                        if (!$objzip->addFile($path)) {
                                                return false;
                                        }
                                }
                        }
                }
                closedir($handle);
        }
        return true;
}

// 
// Create zip-archive. Return true if successful.
// 
function create_zipfile($zipfile, $zipdir)
{
        $objzip = & new ZipArchive();
        if ($objzip->open($zipfile, ZipArchive::CREATE)) {
                if (!add_zipfile($objzip, $zipdir)) {
                        $objzip->close();
                        unlink($zipfile);
                        return false;
                }
        }
        $objzip->close();
        return true;
}

?>
