<?php

// -------------------------------------------------------------------------------
//  Copyright (C) 2007-2008 Anders Lövgren
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
// This is the index page. Installers or integrators should either add an 
// source/index.html to change the index content or define the start page 
// inside conf/config.inc
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
	printf("<h1>Welcome to Batchelor!</h1>\n");
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
    printf("by <a href=\"check.php\">checking your installation</a> and correct any errors found ");
    printf("before continue.</p>\n"); 
    printf("<p>This start page is replaced by either create an <b>index.html</b> in the source ");
    printf("directory or by selecting the start page in conf/config.inc. This page will always ");
    printf("be showed as the start page until BATCHELOR_CONFIGURED is set to true in conf/config.inc.</p>\n");
    printf("<p>You should also create the files <b>about.html</b> (containing a short introduction ");
    printf("to your application) and <b>help.html</b> (a longer tutorial) inside the source directory.</p>\n");
    
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
    printf("is done by editing the command wrapper script (named <b>utils/script.inc</b>). The ");
    printf("default command to run is simula, a queue test program. See INSTALL for build instructions.</p>\n");
    printf("<p>Most people only needs to modify the command to run, this is done by changing the line ");
    printf("that reads:</p>\n");
    printf("<p><code>\n");
    printf("\$(dirname \$0)/simula -i \${QUEUE_INDATA} -r \${QUEUE_RESDIR} 1> \${QUEUE_STDOUT} 2> \${QUEUE_STDERR}\n");
    printf("</code></p>\n");
    printf("<p>Replace 'simula' with your command and make sure the result of it gets saved into the ");
    printf("directory \${QUEUE_RESDIR} (created automatic). If the command to run works as a filter (reads from ");
    printf(" stdin and writes to stdout), then replace the command string with something like this:</p>\n");
    printf("<p><code>\n");
    printf("cat \${QUEUE_INDATA} | command 1> \${QUEUE_RESDIR}/result 2> \${QUEUE_STDERR}; touch \${QUEUE_STDOUT}\n");
    printf("</code></p>\n");
    
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
	if(BATCHELOR_CONFIGURED) {
	    include "index.html";
	} else {
	    print_welcome();
	}
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

// 
// Redirect to real start page if batchelor has been configured.
// 
if(defined("BATCHELOR_CONFIGURED") && defined("BATCHELOR_START_PAGE")) {
    if(BATCHELOR_CONFIGURED) {
	if(BATCHELOR_START_PAGE == "submit") {
	    header("Location: queue.php?show=submit");
	} else if(BATCHELOR_START_PAGE == "queue") {
	    header("Location: queue.php?show=queue");
	} else if(BATCHELOR_START_PAGE == "stats") {
	    header("Location: statistics.php");
	}
    }
}

// 
// Show getting started help.
// 
load_ui_template("popup");

?>
