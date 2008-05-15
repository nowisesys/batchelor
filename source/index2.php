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
// This is the index page. Installers or integrators should add source/index.html
// to change the index content.
// 

// 
// Include configuration and libs.
// 
include "../include/ui.inc";
include "../conf/config.inc";

// 
// The default welcome message displayed if index.html is missing.
// 
function print_welcome()
{
    if(strcasecmp(HTML_PAGE_TITLE, "batchelor") == 0) {
	printf("<h1>Welcome to batchelor!</h1>\n");
    }
    else {
	printf("<h1>Welcome to %s, powered by Batchelor!</h1>\n", HTML_PAGE_TITLE);
    }
    printf("<hr>\n");
    
    printf("<span id=\"secthead\">Congratulations!</span>\n");
    printf("<p>If you see this page, then <b>%s</b> should be up and running on server %s.</p>", 
	   file_get_contents("../VERSION"), $_SERVER['SERVER_ADDR']);
    
    printf("<span id=\"secthead\">The first steps</span>\n");
    printf("<p>The first step is to read README and INSTALL (if not already done). Then continue ");
    printf("by <a href=\"check.php\">checking your installation</a>. Correct any errors found ");
    printf("before continue.</p>\n");
    printf("<p>Now you should continue by customize your installation. The first thing to do ");
    printf("is probably to replace the content of this welcome page. Do so by putting a file ");
    printf("named <b>index.html</b> next to index.php in the source directory. You can also replace ");
    printf("the content of about.php and help.php by adding <b>about.html</b> and <b>help.html</b> ");
    printf("inside the source directory.</p>\n");
    
    printf("<span id=\"secthead\">Modify the User Interface</span>\n");
    printf("<p>The interface of Batchelor can be customized by editing the <b>template/standard.ui</b> ");
    printf("(the standard page layout) and <b>template/popup.ui</b> (used when displaying i.e. job ");
    printf("information). These files are standard HTML pages, so you can edit them inside any editor.</p>\n");
    printf("<p>When edit them, just write the HTML code and insert PHP escape sequences calling ");
    printf("the PHP callback function <b>print_html(...)</b> where you like the output. Valid arguments ");
    printf("for that function are <i>menu</i>, <i>body</i> and <i>footer</i>. Don't change any calls ");
    printf("that uses <i>header</i> as argument as its required by batchelor.</p>\n");
    
    printf("<span id=\"secthead\">Running jobs</span>\n");
    printf("<p>Before you can start submitting jobs, you need to modify the command to run. This ");
    printf("is done by editing the command wrapper script (named <b>utils/script.sh</b>). Just ");
    printf("open it in an editor and modify the command to run. This script gets called with three ");
    printf("arguments:\n");
    printf("<ul>\n");
    printf("  <li>jobdir: the directory where job meta data should be saved</li>\n");
    printf("  <li>indata: the indata file to process</li>\n");
    printf("  <li>resdir: the result directory where output files should go</li>\n");
    printf("</ul></p>\n");
    printf("<p>Most stuff inside utils/script.sh should be left as is. The only thing to change is ");
    printf("the line that reads:</p>\n");
    printf("<p><code>\n");
    printf("'command' \$indata \$resdir 1> \$jobdir/stdout 2> \$jobdir/stderr\n");
    printf("</code></p>\n");
    printf("<p>Replace 'command' with your command and make sure the result of it gets saved into the ");
    printf("directory \$resdir (created automatic). If the command to run works as a filter (reads from ");
    printf(" stdin and writes to stdout), then you can replace the command with something like this:</p>\n");
    printf("<p><code>\n");
    printf("cat \$indata | 'command' 1> \$resdir\output 2> \$jobdir/stderr; touch \$jobdir/stdout\n");
    printf("</code></p>\n");    
    printf("<p>The default command to run is simula, a queue test program. See INSTALL for build instructions.</p>\n");
    
    printf("<span id=\"secthead\">Try it out!</span>\n");
    printf("<p>You can try it out by visiting <a href=\"queue.php?show=submit\">the submit page</a>.</p>\n");
    
    printf("<span id=\"secthead\">Maintenance</span>\n");
    printf("<p>The job directory cache can be cleaned up by periodically running the script <b>cache.php</b> from ");
    printf("inside the utils directory. See usage example by running:</p>\n");
    printf("<p><code>php cache.php --help example</code></p>\n");
    
    printf("<br><i>Good luck! Anders Lövgren.</i>\n");
}

// 
// Display user supplied index.html if exists.
// 
function print_body()
{
    if(file_exists("index.html")) {
	include "index.html";
    }
    else {
	print_welcome();
    }
}

function print_html($what)
{
    switch($what) {
     case "body":
	print_body();
	break;
     case "title":
	printf("%s - Welcome", HTML_PAGE_TITLE);
	break;
     default:
	print_common_html($what);
	break;
    }
}

include "../template/popup.ui";

?>
