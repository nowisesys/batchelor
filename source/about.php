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

// 
// Print about info for batchelor.
// 
function print_about_batchelor()
{
    printf("<span id=\"secthead\">About batchelor</span>\n");
    printf("<p>Batchelor is an batch queue manager meant to allow potentional long ");
    printf("running jobs to be submitted to an web server and scheduled for later ");
    printf("execution thru an batch queue. Users (submitters) can monitor the state ");
    printf("of their submitted jobs (pending, running or finished) and later download ");
    printf("the result from <a href=\"queue?show=queue\">the queue view</a>.</p>\n");
    
    printf("<span id=\"secthead\">Licensing</span>\n");    
    printf("<p>Batchelor is released under <a href=\"about.php?sect=license\">GNU Public License</a> (GPL) and free ");
    printf("for anyone to modify or redistribute. See the file COPYING bundled ");
    printf("together with batchelor. Batchelor is originally developed and copyrighted ");
    printf("&copy; 2007-2008 by Anders Lövgren.</p>\n");

    printf("<span id=\"secthead\">Authors</span>\n");    
    printf("<p>The following people have contributed to batchelor:\n<p>");
    $authors = "../AUTHORS";
    if(file_exists($authors)) {
	$fp = fopen($authors, "r");
	if($fp) {
	    while(($str = fgets($fp))) {
		if($str[0] != '#') {
		    $pattern = '/(.*)\s+<(.*)>\s+-(.*)/';
		    $replace = '${1} <<a href="mailto:${2}">${2}</a>>: ${3}';
		    printf("<code>%s</code><br>\n", preg_replace($pattern, $replace, $str));
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
    printf("</p></p>\n");
    
    printf("<span id=\"secthead\">Bug reports</span>\n");    
    printf("<p>If you think you have found a bug in the batch queue application (batchelor), ");
    printf("then send them to Anders Lövgren &lt;<a href=\"mailto:lespaul@algonet.se\">lespaul@algonet.se</a>&gt;. ");
    printf("Include any error message, the relevant lines from the Apache error log and the output ");
    printf("from check.php (runned from the command line is OK). Use 'batchelor: bug report' as subject line.</p>\n");
}

// 
// Show batchelor license file.
//
function print_license()
{
    $license = "../COPYING";
    if(file_exists($license)) {
	printf("<span id=\"secthead\">GNU Public License</span>\n");
	printf("<p><pre>\n");
	include $license;
	printf("</pre></p>\n");
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
    	if(preg_match("/<body>((.*?|[ \n]*)*)<\/body>/m", $about, $matches)) {
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
    	printf("<span id=\"secthead\">Missing page</span>\n");
	printf("<p>The about page for '%s' is missing. Please check your installation. If this is \n", $desc);	
	printf("a new installation, then create a HTML page named '%s' with information \n", $page);
	printf("about your application or remove this tab menu entry inside about.php.\n");
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
    $tabmenu = array( "appname"   => array( "desc" => "The Application",
					    "func" => null,
					    "page" => "about_app.html",
					    "show" => true ),
		      "batchelor" => array( "desc" => "Batchelor",
					    "func" => "print_about_batchelor",
					    "page" => null,
					    "show" => true ),
		      "license"   => array( "desc" => "GNU Public License",
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

include "../template/popup.ui";

?>
