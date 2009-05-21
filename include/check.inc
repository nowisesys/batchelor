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
// batchelor. All check_xxx function returns true if successful.
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
// Check configuration files.
// 
function check_config_files(&$failed)
{
    print_section_header("Checking configuration files");

    // 
    // The config.inc file is required.
    // 
    output("Checking if config.inc exists: ");
    if(file_exists("../conf/config.inc")) {
	if(filemtime("../conf/config.inc.in") < filemtime("../conf/config.inc")) {
	    success("OK");
	}
	else {
	    warning("File exists but 'conf/config.inc.in' is newer than 'conf/config.inc' (possibly updated)");
	}
    }
    else {
	failed("No, copy 'conf/config.inc.in' to 'conf/config.inc' and edit the file, then rerun the test");
	$failed = true;
    }

    // 
    // The apache.inc may not be used.
    // 
    output("Checking if apache.conf exists: ");
    if(file_exists("../conf/apache.conf")) {
	if(filemtime("../conf/apache.conf.in") < filemtime("../conf/apache.conf")) {
	    success("OK");
	}
	else {
	    warning("File exists but 'conf/apache.conf.in' is newer than 'conf/apache.conf' (possibly updated)");
	}
    }
    else {
	warning("No, if you are using Apache web server, then copy 'conf/apache.conf.in' to 'conf/apache.conf' and edit the file, then rerun the test");
    }
    
    // 
    // The jpgraph.inc is recommended.
    // 
    output("Checking if jpgraph.inc exists: ");
    if(file_exists("../conf/jpgraph.inc")) {
	if(filemtime("../conf/jpgraph.inc.in") < filemtime("../conf/jpgraph.inc")) {
	    success("OK");
	}
	else {
	    warning("File exists but 'conf/jpgraph.inc.in' is newer than 'conf/jpgraph.inc' (possibly updated)");
	}
    }
    else {
	warning("No, if you are planning to use the JpGraph graphics library (used when generating statistics graphs), then copy 'conf/jpgraph.inc.in' to 'conf/jpgraph.inc' and edit the file, then rerun the test");
    }
    
    return $failed == false;
}

// 
// Check utility files.
// 
function check_utility_files(&$failed)
{
    print_section_header("Checking utility files");

    // 
    // The script.sh file is required.
    // 
    output("Checking if script.inc exists: ");
    if(file_exists("../utils/script.inc")) {
	if(filemtime("../utils/script.inc.in") < filemtime("../utils/script.inc")) {
	    success("OK");
	}
	else {
	    warning("File exists but 'utils/script.inc.in' is newer than 'utils/script.inc' (possibly updated)");
	}
    }
    else {
	failed("No, copy 'utils/script.inc.in' to 'utils/script.inc' and edit the file, then run the test again.");
	$failed = true;
    }

    return $failed == false;
}

// 
// Check PHP version.
// 
function check_php_version($failed)
{
    print_section_header("Checking PHP version");
    
    $version = phpversion();
    list($maj, $min, $rev) = explode(".", $version);
    if(strstr($rev, "_")) {
	list($rev, $rel) = explode("_", $rev);
    }
    
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

    return $failed == false;
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
	return true;
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

    return $failed == false;
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
// Check if the given class exists.
// 
function has_class($name)
{
    output("Checking if class $name exists: ");
    
    if(class_exists($name)) {
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

    return $failed == false;
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
		       "strtotime", "filemtime", "error_log", "rename", "array_shift",
		       "array_unshift", "basename", "posix_getpwuid", "posix_getuid",
		       "posix_geteuid", "posix_kill", "ob_start", "ob_end_flush", 
		       "date_default_timezone_get", "timezone_offset_get", "timezone_open");
    sort($functions);
    
    foreach($functions as $func) {
	if(!has_function($func)) {
	    $failed = true;
	}
    }    

    return $failed == false;
}

// 
// Check all required classes.
// 
function check_builtin_classes(&$failed)
{
    print_section_header("Checking required classes");

    $classes = array("DateTime");
    sort($classes);

    foreach($classes as $name) {
	if(!has_class($name)) {
	    $failed = true;
	}
    }    

    return $failed == false;
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

    return $failed == false;
}

// 
// Check required extensions. The list was created by scanning
// the output from running 'using php --rf $func' on all used
// functions, see check_builtin_functions().
// 
function check_loaded_extensions(&$failed)
{
    print_section_header("Checking required extensions");
    
    foreach(array("zip", "posix", "pcre", "date", "standard") as $extension) {
	output("Checking if extension $extension is loaded: ");
	if(extension_loaded($extension)) {
	    success("yes");
	}
	else {
	    failed("no");
	    $failed = true;
	}
    }

    print_section_header("Checking recommended extensions");
    
    foreach(array("soap", "curl") as $extension) {
	output("Checking if extension $extension is loaded: ");
	if(extension_loaded($extension)) {
	    success("yes");
	}
	else {
	    warning("no");
	}
    }

    print_section_header("Checking optional extensions");
    
    foreach(array("wddx") as $extension) {
	output("Checking if extension $extension is loaded: ");
	if(extension_loaded($extension)) {
	    success("yes");
	}
	else {
	    success("no");
	}
    }
    
    return $failed == false;
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

    return $failed == false;
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

    return $failed == false;
}

// 
// Check web service settings.
// 
function check_web_services(&$failed) 
{
    print_section_header("Checking web services");

    // 
    // Enable the HTTP RPC service is strongly recommended.
    // 
    output("Checking the HTTP RPC service: ");
    if(defined("WS_ENABLE_HTTP")) {
	if(WS_ENABLE_HTTP) {
	    success("yes");
	} else {
	    warning("no (not enabled, please enable it or the queue view update will fallback on using HTTP meta refresh (bad))");
	}
    } else {
	failed("no (not defined, please define WS_ENABLE_HTTP in conf/config.inc)");
    }
    
    // 
    // The other services are not that important.
    // 
    output("Checking the REST service: ");
    if(defined("WS_ENABLE_REST")) {
	if(WS_ENABLE_REST) {
	    success("yes");
	} else {
	    success("no");
	}
    } else {
	failed("no (not defined, please define WS_ENABLE_REST in conf/config.inc)");
    }
    output("Checking the SOAP service: ");
    if(defined("WS_ENABLE_SOAP")) {
	if(WS_ENABLE_SOAP) {
	    success("yes");
	} else {
	    success("no");
	}
    } else {
	failed("no (not defined, please define WS_ENABLE_SOAP in conf/config.inc)");
    }
    output("Checking the XML-RPC service: ");
    if(defined("WS_ENABLE_XMLRPC")) {
	if(WS_ENABLE_XMLRPC) {
	    success("yes");
	} else {
	    success("no");
	}
    } else {
	failed("no (not defined, please define WS_ENABLE_XMLRPC in conf/config.inc)");
    }
}

//
// Run all tests.
// 
function run_all_tests()
{
    $failed = false;

    // 
    // This test is fundamental and we should not continue 
    // unless it returns true.
    // 
    if(check_config_files($failed)) {
	include "../conf/config.inc";

	check_utility_files($failed);
	check_php_version($failed);
	check_web_server($failed);
	check_external_commands($failed);
	check_loaded_extensions($failed);
	check_cache_permission($failed);
	check_builtin_functions($failed);
	check_builtin_classes($failed);
	check_predefined_variables($failed);
	check_php_settings($failed);
	check_web_services($failed);
    }
    
    print_section_header("Summary");
    if($failed) {
	output("One or more test has failed, check your system and PHP installation.\n");
    }
    else {
	output("Your PHP installation seems to be OK. Your system seems to be ready for running Batchelor.\n");
    }
}

?>
