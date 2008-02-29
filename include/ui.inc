<?php

// -------------------------------------------------------------------------------
//  Copyright (C) 2008 Anders Lövgren
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
// Common user interface functions.
// 

// 
// Output additional HTTP headers.
// 
function print_header()
{
    printf("<!-- A web application using: batchelor - batch job queue manager -->\n");
    if(isset($GLOBALS['refresh'])) {
	printf("<meta http-equiv=\"refresh\" content=\"%d\" />", PAGE_REFRESH_RATE);
    }
}

// 
// Print menu accoring to user preferences.
// 
function print_menu()
{
    $queue = array( "Submit Jobs" => array( "href" => "queue.php?show=submit", "attr" => "", "icon" => "network.png" ),
		    "Show Queue"  => array( "href" => "queue.php?show=queue",  "attr" => "", "icon" => "services.png" ));
    $utils = array( "Statistics" => array( "href" => "statistics.php", "attr" => "", "icon" => "statistics.png" ),
		    "About" => array( "href" => "about.php", "attr" => "target=\"_blank\"", "icon" => "info.png" ),
		    "Help"  => array( "href" => "help.php",  "attr" => "target=\"_blank\"", "icon" => "help.png" ));
    
    if(!defined(MENU_ORIENTATION)) {
	define ("MENU_ORIENTATION", "vertical");
    }
    if(!defined(MENU_STYLE)) {
	define ("MENU_STYLE", "graphic");
    }
    
    if(MENU_ORIENTATION == "vertical") {
	print_menu_vertical($queue, $utils);
    } 
    else {
	print_menu_horizontal($queue, $utils);
    }
}

// 
// Print vertical styles menu, suitable for a left-side menu.
// 
function print_menu_vertical($queue, $utils)
{
    if(MENU_STYLE == "graphic") {
	echo "<div class=\"menuhead\"><img src=\"icons/nuvola/users.png\">&nbsp;&nbsp;Job Queue</div>\n";
    }
    echo "<ul class=\"menu\">\n";
    foreach($queue as $name => $link) {
	printf("<li><a href=\"%s\" %s>%s</a></li>\n", $link['href'], $link['attr'], $name);
    }
    if(MENU_STYLE != "compact") {
	echo "</ul>\n";
    }

    if(MENU_STYLE == "graphic") {
	echo "<div class=\"menuhead\"><img src=\"icons/nuvola/utils.png\">&nbsp;&nbsp;Utilities</div>\n";
    }
    if(MENU_STYLE != "compact") {	
	echo "<ul class=\"menu\">\n";
    }
    foreach($utils as $name => $link) {
	printf("<li><a href=\"%s\" %s>%s</a></li>\n", $link['href'], $link['attr'], $name);
    }
    echo "</ul>\n";
}

// 
// Print horizontal menu, suitable for a top-menu.
//
function print_menu_horizontal($queue, $utils)
{
    echo "<table><tr>\n";
    foreach($queue as $name => $opts) {
	echo "<td class=\"topmenuitem\">\n";
	if(MENU_STYLE == "graphic") {
	    printf("<img src=\"icons/nuvola/%s\">", $opts['icon']);
	}
	printf("<a href=\"%s\" %s>%s</a>", $opts['href'], $opts['attr'], $name);
	echo "</td>\n";
    }
    if(MENU_STYLE != "compact") {
	echo "<td width=\"60\">\n";
    }
    foreach($utils as $name => $opts) {
	echo "<td class=\"topmenuitem\">\n";
	if(MENU_STYLE == "graphic") {
	    printf("<img src=\"icons/nuvola/%s\">", $opts['icon']);
	}
	printf("<a href=\"%s\" %s>%s</a>", $opts['href'], $opts['attr'], $name);
	echo "</td>\n";
    }
    echo "</tr></table>\n";
}

// 
// Print page footer.
// 
function print_footer()
{
    print "<table width=\"90%\"><tr>\n";
    printf("<td class=\"footer\">Last updated: %s<br>Contact: %s</td>\n", format_timestamp(time()), CONTACT_STRING);
    print "<td class=\"copyright\">Copyright &copy; 2007-2008 Anders Lövgren</td>\n";
    print "</tr></table>\n";
}

function print_common_html($what)
{
    switch($what) {
     case "header":
	print_header();
	break;
     case "menu":
	print_menu();
	break;
     case "footer":
	print_footer();
	break;
    }
}
