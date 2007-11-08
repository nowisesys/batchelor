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
// Print warning message.
// 
function warning($str)
{
    if(WPRINTF_MODE == "www") {
	print "<font color=\"blue\">$str</font><br>";
    }
    else {
	print "$str (!)\n";
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
	    $failed = true;
	}
	else {
	    failed(sprintf("%d.%d.%d, this version is ancient", $maj, $min, $rev));
	    $failed = true;
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
// Check the web server.
// 
function check_web_server(&$failed)
{    
    // 
    // Skip test if running under CLI.
    // 
    if(WPRINTF_MODE == "cli") {
	return;
    }

    print_section_header("Checking web server");

    output("Checking web server type: ");
    if(isset($_SERVER['SERVER_SIGNATURE'])) {
	success(strip_tags($_SERVER['SERVER_SIGNATURE']));
    }
    else {
	if(isset($_SERVER['SERVER_SOFTWARE'])) {
	    success($_SERVER['SERVER_SOFTWARE']);
	}
	else {
	    warning("undefined");
	}
    }
    
    output("Checking web server user: ");
    $user = posix_getpwuid(posix_getuid());
    $real = posix_getpwuid(posix_geteuid());
    success(sprintf("real=%s, effective=%s", $real['name'], $user['name']));
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
	$failed = true;
    }

    output("Checking if \$_FILES is defined: ");
    if(isset($_FILES)) {
	success("yes");
    }
    else {
	failed("no");
	$failed = true;
    }

    output("Checking if \$_REQUEST is defined: ");
    if(isset($_REQUEST)) {
	success("yes");
    }
    else {
	failed("no");
	$failed = true;
    }

    output("Checking if \$_COOKIE is defined: ");
    if(isset($_COOKIE)) {
	success("yes");
    }
    else {
	failed("no");
	$failed = true;
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
		$failed = true;
	    }

	    output("Checking if cache directory is writable: ");
	    if(is_writable(CACHE_DIRECTORY)) {
		success("yes");
	    }
	    else {
		failed("no");
		$failed = true;
	    }
	}
	else {
	    failed(sprintf("%s exists, but is not a directory", CACHE_DIRECTORY));
	    $failed = true;
	}
    }
}

// 
// Check PHP settings, some of them is more like an recommendation.
// 
function check_php_settings(&$failed)
{
    print_section_header("Checking PHP settings");
    
    output("Checking if timezone is set: ");
    if(ini_get("date.timezone")) {
	success(sprintf("yes (%s)", ini_get("date.timezone")));
    }
    else {
	warning("no, consider define your timezone either system wide (in php.ini) or application local (in config.inc)");
    }
    
    output("Checking if contact address is set: ");
    if(CONTACT_ADDRESS != "batchelor@localhost") {
	success(sprintf("yes, %s", CONTACT_ADDRESS));
    }
    else {
	warning(sprintf("no, using %s defined in config.inc", CONTACT_ADDRESS));
    }
    
    output("Checking maximum filesize for uploaded files: ");
    $max_filesize = ini_get("upload_max_filesize") * 1024 * 1024;
    if(UPLOAD_MAX_FILESIZE <= $max_filesize) {
	if(UPLOAD_MAX_FILESIZE > 0) {
	    success(sprintf("%s (from UPLOAD_MAX_FILESIZE in config.inc)", bytes_to_string(UPLOAD_MAX_FILESIZE)));
	}
	else {
	    success(sprintf("%s (from upload_max_filesize in php.ini)", bytes_to_string($max_filesize)));
	}
    }
    else {
	warning(sprintf("the UPLOAD_MAX_FILESIZE (%s) is larger that upload_max_filesize (%s) defined in php.ini",
			bytes_to_string(UPLOAD_MAX_FILESIZE), bytes_to_string($max_filesize)));
    }
    
    output("Checking if meta refresh page is used: ");
    if(PAGE_REFRESH_RATE == 0) {
	success("no");
    }
    else {
	warning(sprintf("yes (%d sec). Using meta refresh may cause problem with file uploads.", 
			PAGE_REFRESH_RATE));
    }
    
    output("Checking if session cookies is enabled: ");
    if(USE_SESSION_COOKIES) {
	warning("yes (cookie is removed when browser is closed)");
    }
    else {
	success("no (using persistent cookies)");
	output("Checking persistent cookies lifetime: ");	
	success(seconds_to_string(COOKIE_LIFE_TIME));
    }
}

//
// Run all tests.
// 
function run_all_tests()
{
    $failed = false;

    check_php_version($failed);
    check_web_server($failed);
    check_external_commands($failed);
    check_loaded_extensions($failed);
    check_cache_permission($failed);
    check_builtin_functions($failed);
    check_predefined_variables($failed);
    check_php_settings($failed);
    
    print_section_header("Summary");
    if($failed) {
	output("One or more test failed, check your PHP installation.\n");
    }
    else {
	output("Your PHP installation seems to be OK.\n");
    }
}

?>