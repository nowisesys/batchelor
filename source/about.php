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
// to source/about.html.
// 
// See README before you modify the Licensing or Authors section!
// 

// 
// Include configuration and libs.
// 
include "../include/ui.inc";

// 
// The about message with additional content from about.html is existing.
// 
function print_about()
{
    printf("<h1>About batchelor</h1>\n");
    printf("<hr>\n");

    if(file_exists("about.html")) {
	printf("<span id=\"secthead\">Additional notes</span>\n");
	include "about.html";
    }
    
    printf("<span id=\"secthead\">About batchelor</span>\n");
    printf("<p>Batchelor is an batch queue manager meant to allow potentional long ");
    printf("running jobs to be submitted to an web server and scheduled for later ");
    printf("execution thru an batch queue. Users (submitters) can monitor the state ");
    printf("of their submitted jobs (pending, running or finished) and later download ");
    printf("the result from <a href=\"queue?show=queue\">the queue view</a>.</p>\n");
    
    printf("<span id=\"secthead\">Licensing</span>\n");    
    printf("<p>Batchelor is released under GNU Public License (GPL) and free ");
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

function print_html($what)
{
    switch($what) {
     case "body":
	print_about();
	break;
     case "title":
	print "Batchelor - About";
	break;
     default:
	print_common_html($what);
	break;
    }
}

include "../template/popup.ui";

?>
