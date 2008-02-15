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
// Create zip-archive. Return true if successful.
// 
function create_zipfile($zipfile, $zipdir)
{
    $zip = new ZipArchive();	 	
    if($zip->open($zipfile, ZipArchive::CREATE)) {
	$dir = opendir($zipdir);
	if($dir) {
	    while(false !== ($file = readdir($dir))) {
		if($file != "." && $file != "..") {
		    if(!$zip->addFile(sprintf("%s/%s", $zipdir, $file))) {
			$zip->close();
			unlink($zipfile);
			closedir($dir);
			return false;
		    }
		}
	    }
	    closedir($dir);
	}
	else {
	    $zip->close();
	    unlink($zipfile);
	    return false;
	}
	$zip->close();
	return true;
    }
    else {
	return false;
    }
}

?>
