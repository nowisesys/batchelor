<?php

// -------------------------------------------------------------------------------
//  Copyright (C) 2007-2009 Anders Lövgren
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
// Shows information on the web service ... 
// 

ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . "../../.." . PATH_SEPARATOR . "..");

define("WS_NAME", "SOAP");

//
// Get configuration.
// 
include "conf/config.inc";
include "include/ui.inc";
include "include/soap.inc";

function print_title()
{
        printf("%s - Web Services API (%s) - Method and types", HTML_PAGE_TITLE, WS_NAME);
}

function print_body()
{
        printf("<h2><img src=\"../../icons/nuvola/info.png\"> %s - Web Services API (%s) - Methods and Types</h2>\n", HTML_PAGE_TITLE, WS_NAME);
        echo "<span id=\"secthead\">Introduction:</span>\n";
        echo "<p>The types passed as argument to methods and the result from the called method are <u>always</u> ";
        echo "wrapped in an object structure (possibly empty, see the version method). If you are using a language ";
        echo "with object to XML mapping, then this list can be rather helpful to use as a guideline.</p>\n";

        echo "<span id=\"secthead\">Types:</span>\n";
        echo "<p>These are the method argument and response types:</p>\n";
        echo "<p><div class=\"code\"><pre>\n";
        echo <<< EOF
    struct resume {
	 jobidentity job;
    }
    
    struct jobidentity {
	 string jobid;
	 string _result;
    }
    
    struct resume_response {
	 boolean return;
    }
    
    struct suspend {
	 jobidentity job;
    }
    
    struct suspend_response {
	 boolean return;
    }
    
    struct queue {
	 string sort;
	 string filter;
    }
    
    struct queue_response {
	 queuedjob return;
    }
    
    struct queuedjob {
	 jobidentity jobidentity;
	 string state;
    }
    
    struct enqueue {
	 string indata;
    }
    
    struct enqueue_response {
	 enqueue_result return;
    }
    
    struct enqueue_result {
	 string date;
	 string jobid;
	 string _result;
	 int stamp;
	 string time;
    }
    
    struct watch {
	 int stamp;
    }
    
    struct watch_response {
	 queuedjob return;
    }
    
    struct fopen {
	 jobidentity job;
	 string file;
    }
    
    struct fopen_response {
	 base64binary return;
    }
    
    struct opendir {
    }
    
    struct opendir_response {
	 jobidentity return;
    }
    
    struct stat {
	 jobidentity job;
    }
    
    struct stat_response {
	 queuedjob return;
    }
    
    struct readdir {
	 jobidentity job;
    }
    
    struct readdir_response {
	 string return;
    }
    
    struct dequeue {
	 jobidentity job;
    }
    
    struct dequeue_response {
	 boolean return;
    }
    
    struct version {
    }
    
    struct version_response {
	 string return;
    }
EOF;
        echo "</pre></div></p>\n";

        echo "<span id=\"secthead\">Methods:</span>\n";
        echo "<p>These are the methods showing their argument and response types:</p>\n";
        echo "<p><div class=\"code\"><pre>\n";
        echo <<< EOF
    enqueue_response enqueue(enqueue obj)
    queue_response queue(queue obj)
    resume_response resume(resume obj)
    suspend_response suspend(suspend obj)
    version_response version(version obj)
    dequeue_response dequeue(dequeue obj)
    watch_response watch(watch obj)
    opendir_response opendir(opendir obj)
    readdir_response readdir(readdir obj)
    fopen_response fopen(fopen obj)
    stat_response stat(stat obj)
EOF;
        echo "</pre></div></p>\n";
}

function print_html($what)
{
        switch ($what) {
                case "body":
                        print_body();
                        break;
                case "title":
                        print_title();
                        break;
                default:
                        print_common_html($what);    // Use default callback.
                        break;
        }
}

chdir("../..");
load_ui_template("apidoc");

