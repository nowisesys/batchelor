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
// This is the help page. Installers or integrators should add source/help.html
// to change the help content.
// 

// 
// Include configuration and libs.
// 
include "../include/ui.inc";
include "../conf/config.inc";

// 
// The default help message displayed if help.html is missing.
// 
function print_help()
{
    printf("<h2><img src=\"icons/nuvola/help.png\"> Help contents</h2>\n");
    
    printf("<span id=\"secthead\">General</span>\n");
    printf("<p>You see this page because no installation specific help has been written (yet). ");
    printf("If this is your installation then create the file <b>source/help.html</b> giving ");
    printf("information on how to use your batch queue, i.e. describing the accepted format ");
    printf("of uploaded data.</p>\n");
    
    printf("<span id=\"secthead\">Reporting bugs</span>\n");
    printf("<p>If you think you found a bug in batchelor, please visit the <a href=\"about.php\">");
    printf("about</a> page for instructions on how to send a bug report.</p>\n");
}

// 
// Display user supplied help.html if it exists.
// 
function print_body()
{
    if(file_exists("help.html")) {
	$help = file_get_contents("help.html");
	$matches = array();
	if(preg_match("/<body.*?>((.*?|[ \n]*)*)<\/body>/mi", $help, $matches)) {
	    print $matches[1];
	}
	else {
	    print "<h2><img src=\"icons/nuvola/error.png\"> Failed match pattern.</h2>\n"; 
	    print "The file help.html should be a complete HTML page, including headers.";
	}
    }
    else {
	print_help();
    }
}

function print_html($what)
{
    switch($what) {
     case "body":
	print_body();
	break;
     case "title":
	printf("%s - Help", HTML_PAGE_TITLE);
	break;
     default:
	print_common_html($what);
	break;
    }
}

load_ui_template("popup");

?>
