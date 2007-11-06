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
// Functions for checking if the system is up to date for running 
// batchelor. The functions takes an callback function argument
// that is called to print the response. All function returns true
// if successful.
// 

// 
// Print success message.
// 
function success($str)
{
    if(WPRINTF_MODE == "www") {
	print "<font color=\"green\">$str</font><br>";
    }
    else {
	print "$str (+)\n";
    }
}

// 
// Print failed message.
// 
function failed($str)
{
    if(WPRINTF_MODE == "www") {
	print "<font color=\"red\">$str</font><br>";
    }
    else {
	print "$str (-)\n";
    }
}

// 
// Generic output.
// 
function output($str, $emphase = false)
{
    if(WPRINTF_MODE == "www") {
	$str = str_replace("\n", "<br>", $str);
	if($emphase) {
	    print "<b>$str</b>";
	}
	else {
	    print "$str";
	}
    }
    else {
	print "$str";
    }
}

// 
// Print section header.
// 
function print_section_header($str)
{
    output("\n$str:\n", true);
    $strlen = strlen($str);
    for($i = 0; $i < $strlen + 2; $i++) {
	output("-");
    }
    output("\n");
}

// 
// Check PHP version.
// 
function check_php_version($failed)
{
    print_section_header("Checking PHP version");
    
    $version = phpversion();
    list($maj, $min, $rev) = explode(".", $version);
    list($rev, $rel) = explode("_", $rev);
    
    output("PHP version is: ");
    if($maj >= 5) {
	if(isset($rel)) {
	    success(sprintf("%d.%d.%d (release=%s), should be OK", $maj, $min, $rev, $rel));
	}
	else {
	    success(sprintf("%d.%d.%d, should be OK", $maj, $min, $rev));
	}
    }
    else {
	if(isset($rel)) {
	    failed(sprintf("%d.%d.%d (release=%s), this version is ancient", $maj, $min, $rev, $rel));
	}
	else {
	    failed(sprintf("%d.%d.%d, this version is ancient", $maj, $min, $rev));
	}
	failed("Some required functions will probably be missing or not working as expected. Running an old version of PHP may also expose you to sequrity holes.");
	if(WPRINTF_MODE == "www") {
	    output("\nPlease upgrade to latest PHP release from: <a href=\"http://www.php.net\" target=\"_blank\">http://www.php.net</a>\n", true);
	}
	else {
	    output("\nPlease upgrade to latest PHP release from: http://www.php.net\n", true);
	}
    }
}

// 
// Check if command is in path. This will only work if script is run 
// in a POSIX environment as Unix/Linux or Cygwin on Windows.
//
function has_command($command)
{
    output("Looking for $command: ");
    
    $output = "";
    $return = 0;
      
    exec("which $command 2> /dev/null", $output, $return);
    
    if($return == 0) {
	success($output[0]);
	return true;
    }

    failed("not found");
    return false;
}

// 
// Check if the given function exists.
// 
function has_function($name)
{
    output("Checking if function $name exists: ");
    
    if(function_exists($name)) {
	success("yes");
	return true;
    }
    
    failed("no");
    return false;
}

// 
// Check all required external commands.
//
function check_external_commands(&$failed)
{
    print_section_header("Checking external commands");
    
    foreach(array("at", "atrm", "batch") as $command) {
	if(!has_command($command)) {
	    $failed = true;
	}
    }
}

// 
// Check all required functions.
// 
function check_builtin_functions(&$failed)
{
    print_section_header("Checking required functions");
    
    $functions = array("printf", "count", "setcookie", "file_exists", "strftime",
		       "sprintf", "array_push", "implode", "mkdir", "file_put_contents", 
		       "is_uploaded_file", "filesize", "unlink", "rmdir", "realpath", 
		       "dirname", "file_get_contents", "header", "opendir", "readdir", 
		       "closedir", "is_file", "is_dir", "is_link", "popen", "pclose", 
		       "fwrite", "fgets", "fread", "readfile", "getcwd", "chdir", "trim", 
		       "extension_loaded", "version_compare", "proc_open", "is_resource", 
		       "stream_get_contents", "fclose", "proc_close", "preg_match",
		       "strtotime", "filemtime", "error_log", "rename");
    sort($functions);
    
    foreach($functions as $func) {
	if(!has_function($func)) {
	    $failed = true;
	}
    }    
}

// 
// Check predefined variables. These variables are dunamic by
// nature, so we can't test their characteristics (like if $_FILES
// defines the error code). We can only test for their presence.
//
function check_predefined_variables(&$failed)
{
    print_section_header("Checking predefined variables");
    
    output("Checking if \$_SERVER is defined: ");
    if(isset($_SERVER)) {
	success("yes");
    }
    else {
	failed("no");
    }

    output("Checking if \$_FILES is defined: ");
    if(isset($_FILES)) {
	success("yes");
    }
    else {
	failed("no");
    }

    output("Checking if \$_REQUEST is defined: ");
    if(isset($_REQUEST)) {
	success("yes");
    }
    else {
	failed("no");
    }

    output("Checking if \$_COOKIE is defined: ");
    if(isset($_COOKIE)) {
	success("yes");
    }
    else {
	failed("no");
    }
}

// 
// Check required extensions.
// 
function check_loaded_extensions(&$failed)
{
    print_section_header("Checking loaded extensions");
    
    foreach(array("zip") as $extension) {
	output("Checking if extension $extension is loaded: ");
	if(extension_loaded($extension)) {
	    success("yes");
	}
	else {
	    failed("no");
	    $failed = true;
	}
    }
}

// 
// Check permission on cache directory.
// 
function check_cache_permission(&$failed)
{
    print_section_header("Checking cache directory");

    output("Checking if cache directory exist: ");
    if(!file_exists(CACHE_DIRECTORY)) {
	failed("no");
	$failed = true;
    }
    else {
	if(is_dir(CACHE_DIRECTORY) || is_link(CACHE_DIRECTORY)) {
	    success(sprintf("yes, %s", CACHE_DIRECTORY));
	    
	    output("Checking if cache directory is readable: ");
	    if(is_readable(CACHE_DIRECTORY)) {
		success("yes");
	    }
	    else {
		failed("no");
	    }

	    output("Checking if cache directory is writable: ");
	    if(is_writable(CACHE_DIRECTORY)) {
		success("yes");
	    }
	    else {
		failed("no");
	    }
	}
	else {
	    failed(sprintf("%s exists, but is not a directory", CACHE_DIRECTORY));
	}
    }
}

//
// Run all tests.
// 
function run_all_tests()
{
    $failed = false;

    check_php_version($failed);
    check_external_commands($failed);
    check_loaded_extensions($failed);
    check_cache_permission($failed);
    check_builtin_functions($failed);
    check_predefined_variables($failed);
    // check_internal_functions($failed);
    
    if($failed) {
	print_section_header("Summary");
	output("One or more test failed, check your PHP installation.\n");
    }
}

?>
