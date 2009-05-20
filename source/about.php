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
// This is the about page. Installers or integrators should add additional content
// to source/about_xxx.html. See $tabmeny() in function print_about().
// 
// See README before you modify the Licensing or Authors section!
// 

// 
// Include configuration and libs.
// 
include "../include/ui.inc";
include "../conf/config.inc";

if(!defined("WS_ENABLE_HTTP")) {
    define("WS_ENABLE_HTTP", null);
}
if(!defined("WS_ENABLE_SOAP")) {
    define("WS_ENABLE_SOAP", null);
}
if(!defined("WS_ENABLE_REST")) {
    define("WS_ENABLE_REST", null);
}
if(!defined("WS_ENABLE_XMLRPC")) {
    define("WS_ENABLE_XMLRPC", null);
}

// 
// Print about info for batchelor.
// 
function print_about_batchelor()
{
    print "<span id=\"secthead\">About Batchelor</span>\n";
    print "<p>This web application is powered by Batchelor, a batch job queue manager written in PHP.</p>\n";
    print "<p>Using a batch queue manager allows jobs with long run times ";
    print "to be submitted to the web server and scheduled for later ";
    print "execution by its batch queue. Users (submitters) can monitor the state ";
    print "of their submitted jobs (pending, running or finished) and later download ";
    print "the result from <a href=\"queue?show=queue\">the queue view</a>.</p>\n";
    print "<p><b>Features of Batchelor:</b>\n";
    print "<ul><li>Easy to install, extend and customize.</li>\n";
    print "    <li>Template system for changing the user interface.</li>\n";
    print "    <li><a href=\"ws/docs/\">Web service interface</a> (SOAP, REST, HTTP RPC and XML-RPC)</li>\n";
    print "    <li>No database is required.</li>";
    print "    <li>Works with all browsers, even text-based (no javascript required).</li>\n";
    print "    <li>Supports system wide and personal statistics.</li>\n";
    print "</ul></p>\n";
    
    print "<span id=\"secthead\">Licensing</span>\n";    
    print "<p>Batchelor is released under the <a href=\"about.php?sect=license\">GNU Public License</a> (GPL) and free ";
    print "for anyone to modify or redistribute. See the file COPYING bundled ";
    print "together with Batchelor for details. Batchelor is originally developed Anders L&ouml;vgren.</p>\n";

    print "<span id=\"secthead\">Authors</span>\n";    
    print "<p>The following people have contributed to Batchelor:\n</p><p>";
    $authors = "../AUTHORS";
    if(file_exists($authors)) {
	$fp = fopen($authors, "r");
	if($fp) {
	    while(($str = fgets($fp))) {
		if($str[0] != '#') {
		    $pattern = '/(.*)\s+<(.*)>\s+-(.*)/';
		    $replace = '${1} <<a href="mailto:${2}">${2}</a>>: ${3}';
		    printf("<code>%s</code><br>\n", strtr(preg_replace($pattern, $replace, $str),
							  array( "å" => "&aring;", "ä" => "&auml;", "ö" => "&ouml;" )));
		}
	    }
	    fclose($fp);
	}
	else {
	    printf("<b>Failed open %s, check your installation!</b>\n", $authors);
	}
    }
    else {
	printf("<b>File %s is missing, check your installation!</b>\n", $authors);
    }
    print "</p>\n";

    print "<span id=\"secthead\">Copyright</span>\n";
    print "<p>Batchelor is Copyright &copy; 2007-2009 by Anders L&ouml;vgren and the ";
    print "<a href=\"http://www.bmc.uu.se/node151\">Computing Department</a> ";
    print "at <a href=\"http://www.bmc.uu.se\">Uppsala Biomedical Centre</a>, Uppsala University (with equally shared rights).</p>\n";
        
    $credits = "../CREDITS";
    if(file_exists($credits)) {
	$fp = fopen($credits, "r");
	if($fp) {
	    $head = "";
	    $package = array();
	    while(($str = fgets($fp))) {
		$str = trim($str);
		if(strlen($str)) {
		    $match = array();
		    if(preg_match("/^\*\s+(.*?):/", $str, $match)) {
			$head = $match[1];
		    }
		    else if(preg_match("/^(http[s]{0,1}:\/\/.*)/", $str, $match)) {
			$package[$head][] = $match[1];
		    }
		}
	    }
	    fclose($fp);
	    print "<span id=\"secthead\">Credits</span>\n";
	    // print "<p>Thanks to the <a href=\"http://www.bmc.uu.se/node151\">Computing Department at BMC</a> for allocating time for me to work on and extend this project.</p>\n";
	    if(count($package)) {
		print "<p>The following other projects have been used in Batchelor:\n</p>";
		foreach($package as $name => $links) {
		    print "<p><u>$name:</u><ul>\n";
		    foreach($links as $link) {
			print "<li><a href=\"$link\">$link</a></li>\n";
		    }
		    print "</ul></p>\n";
		}
	    }
	}
	else {
	    printf("<b>Failed open %s, check your installation!</b>\n", $authors);
	}	
    }
    
    print "<span id=\"secthead\">Download</span>\n";
    print "<p>The latest version of Batchelor can be downloaded from <a href=\"http://it.bmc.uu.se/andlov/proj/batchelor/\">http://it.bmc.uu.se/andlov/proj/batchelor/</a></p>\n";
    
    print "<span id=\"secthead\">Bug reports</span>\n";
    print "<p>If you think you have found a bug in the batch queue manager (Batchelor), ";
    print "please send a bug report to Anders L&ouml;vgren &lt;<a href=\"mailto:lespaul@algonet.se\">lespaul@algonet.se</a>&gt;. ";
    print "Include any error message, all relevant lines from the Apache error log and the output ";
    print "from check.php (runned from the command line is OK). Use 'batchelor: bug report' as subject line.</p>\n";
}

// 
// Print about info on web services (SOAP, REST, HTTP RPC and XML-RPC).
// 
function print_webservices() 
{    
    $status = array(
		    "rest"   => array( "status" => WS_ENABLE_REST,
				       "name"   => "REST",
				       "link"   => "ws/docs/rest.php" ),
		    "soap"   => array( "status" => WS_ENABLE_SOAP,
				       "name"   => "SOAP",
				       "link"   => "ws/docs/soap.php" ),
		    "http"   => array( "status" => WS_ENABLE_HTTP,
				       "name"   => "HTTP RPC",
				       "link"   => "ws/docs/http.php" ),
		    "xmlrpc" => array( "status" => WS_ENABLE_XMLRPC,
				       "name"   => "XML-RPC",
				       "link"   => "ws/docs/xmlrpc.php" )
		);
    
    echo "<span id=\"secthead\">Status:</span>\n";
    echo "<p>\n";
    foreach($status as $name => $data) {
	if(file_exists(sprintf("ws/%s", $name))) {
	    if(isset($data['status'])) {
		if($data['status']) {
		    printf("<img src=\"icons/nuvola/enabled.png\"><a href=\"%s\"> %s</a> is enabled.<br>\n", 
			   $data['link'], $data['name']);
		} else {
		    printf("<img src=\"icons/nuvola/disabled.png\"><a href=\"%s\"> %s</a> is disabled.<br>\n", 
			   $data['link'], $data['name']);
		}
	    } else {
		printf("<img src=\"icons/nuvola/unconfigured.png\"><a href=\"%s\"> %s</a> is not configured.<br>\n", 
		       $data['link'], $data['name']);
	    }
	}
    }
    echo "</p>\n";
    echo "<p>These are the web services that are currently enabled. Click on the link next to the status ";
    echo "icon to read the documentation for respective service type.<br>\n";
    echo "See <a href=\"ws/docs/intro.php\">introduction</a> for getting started information.</p>\n";
}

// 
// Show batchelor license file.
//
function print_license()
{
    $license = "../COPYING";
    if(file_exists($license)) {
	print "<span id=\"secthead\">GNU Public License</span>\n";
	print "<p><pre>\n";
	include $license;
	print "</pre></p>\n";
    }
}

// 
// Print an external HTML page.
// 
function print_about_page($page, $desc)
{
    if(file_exists($page)) {
    	$about = file_get_contents($page);
    	$matches = array();
    	if(preg_match("/<body.*?>((.*?|[ \n]*)*)<\/body>/mi", $about, $matches)) {
    	    print $matches[1];
    	}
    	else {
    	    print "<span id=\"secthead\">Failed match pattern.</span>\n"; 
    	    print "<p>The file $path should be a complete HTML page, including headers with one or \n";
	    print "more sections (inside span tags with id secthead). Each section should have one \n";
	    print "or more paragraphs.\n";
	}
    }
    else {
    	print "<span id=\"secthead\">Missing page</span>\n";
	printf("<p>The about page for '%s' is missing. Please check your installation. If this is \n", $desc);	
	printf("a new installation, then create a HTML page named '%s' with information \n", $page);
	print "about your application or remove this tab menu entry inside about.php.\n";
    }
}

// 
// Print the tab menu.
// 
function print_about_menu(&$map, $selected)
{    
    print "<div id=\"tabmenu\"><ul>\n";
    foreach($map as $name => $entry) {
	if($entry['show']) {
	    if($name == $selected) {
		printf("<li id=\"selected\"><a href=\"about.php?sect=%s\">%s</a></li><!-- Fix IE\n -->\n",
		       $name, $entry['desc']);
	    }
	    else {
		printf("<li><a href=\"about.php?sect=%s\">%s</a></li><!-- Fix IE\n -->\n",
		       $name, $entry['desc']);
	    }
	}
    }
    print "</div></u>\n";
}

// 
// The about message with additional content from about_xxx.html is existing.
// 
function print_about()
{
    // 
    // Add pages to the tab menu. You can add your own tabs by either:
    //   1. Add an function in this file (like for batchelor).
    //   2. Add an external page (like the app example).
    //
    $tabmenu = array( "appname"    => array( "desc" => "The Application",
					     "func" => null,
					     "page" => "about_app.html",
					     "show" => true ),
		      "batchelor"  => array( "desc" => "Batchelor",
					     "func" => "print_about_batchelor",
					     "page" => null,
					     "show" => true ),
		      "webservice" => array( "desc" => "Web Services",
					     "func" => "print_webservices",
					     "page" => null,
					     "show" => true ),
		      "license"    => array( "desc" => "GNU Public License",
					     "func" => "print_license",
					     "page" => null,
					     "show" => false ));
    
    $selected = "appname";
    if(isset($_REQUEST['sect'])) {
	$selected = $_REQUEST['sect'];
    }
    
    printf("<h2><img src=\"icons/nuvola/info.png\"> About %s</h2>\n", $tabmenu[$selected]['desc']);
    printf("<hr>\n");
    
    // 
    // Print tab menu:
    // 
    print_about_menu($tabmenu, $selected);
    
    // 
    // Print page body:
    // 
    print "<div class=\"body\"><br>\n";
    if(isset($tabmenu[$selected]['page'])) {
	print_about_page($tabmenu[$selected]['page'],
			 $tabmenu[$selected]['desc']);
    }
    else if(isset($tabmenu[$selected]['func'])) {
	$func = $tabmenu[$selected]['func'];
	$func();
    }
    print "</div>\n";
    print "<br>\n";
}

function print_html($what)
{
    switch($what) {
     case "body":
	print_about();
	break;
     case "title":
	printf("%s - About", HTML_PAGE_TITLE);
	break;
     default:
	print_common_html($what);
	break;
    }
}

load_ui_template("popup");

?>
