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
// Replacement versions for the following functions:
// * file_put_contents()        // PHP >= 5.0.0
// * stream_get_contents()      // PHP >= 5.0.0
// 

// 
// Required constants (from ext/standard/file.h in PHP 5):
// 
if(!defined("FILE_USE_INCLUDE_PATH")) {
    define ("FILE_USE_INCLUDE_PATH", 1);
}
if(!defined("FILE_APPEND")) {
    define ("FILE_APPEND", 8);
}

// 
// Write a string to a file (PHP 5) 
// int file_put_contents ( string filename, mixed data [, int flags [, resource context]] )
// 
function file_put_contents($filename, $data, $flags = 0, $context = null)
{
    $include = false;
    $mode = "w";
    $bytes = 0;
    
    if($flags & FILE_USE_INCLUDE_PATH) {
	$include = true;
    }
    if($flags & FILE_APPEND) {
	$mode = "a";
    }
    
    if(isset($context)) {
	$handle = fopen($filename, $mode, $include, $context);
    }
    else {
	$handle = fopen($filename, $mode, $include);
    }
    
    if($handle) {
	if($flags & LOCK_EX) {
	    if(!flock($handle, LOCK_EX)) {
		fclose($handle);
		return false;
	    }
	}
	if(is_array($data)) {
	    $data = implode('', $data);
	}
	$bytes = fwrite($handle, $data);
	if(!$bytes) {
	    fclose($handle);
	    return false;
	}
	if($bytes != strlen($data)) {
	    fclose($handle);
	    return false;
	}
	fclose($handle);
    }
    else {
	return false;
    }
    
    return $bytes;
}

// 
// Reads remainder of a stream into a string (PHP 5) 
// string stream_get_contents ( resource handle [, int maxlength [, int offset]] )
//
function stream_get_contents($handle, $length = -1, $offset = 0)
{
    if($offset != 0) {
	if(fseek($handle, $offset, SEEK_CUR) < 0) {
	    return false;
	}
    }
    if($length != -1) {
	return fread($handle, $length);
    }
    else {
	$result = "";
	while(!feof($handle)) {
	    $str = fread($handle, 4096);
	    if($str) {
		$result .= $str;
	    }
	}
	return $result;
    }
}

?>
