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
// Print left-side menu. Add additional links to the array.
// 
function print_menu()
{
    // echo "<div class=\"menu\">\n";
    
    $links = array( "Submit Jobs" => array( "href" => "queue.php?show=submit", "attr" => "" ),
		    "Show Queue"  => array( "href" => "queue.php?show=queue",  "attr" => "" ));
    
    echo "<div class=\"menuhead\"><img src=\"icons/nuvola/users.png\">&nbsp;&nbsp;Job Queue</div>\n";
    echo "<ul class=\"menu\">\n";
    foreach($links as $name => $link) {
	printf("<li><a href=\"%s\" %s>%s</a></li>\n", $link['href'], $link['attr'], $name);
    }
    echo "</ul>\n";
    // echo "</div>\n";

    $links = array( "About" => array( "href" => "about.php", "attr" => "target=\"_blank\"" ),
		    "Help"  => array( "href" => "help.php",  "attr" => "target=\"_blank\"" ));
    
    echo "<div class=\"menuhead\"><img src=\"icons/nuvola/utils.png\">&nbsp;&nbsp;Utilities</div>\n";
    echo "<ul class=\"menu\">\n";
    foreach($links as $name => $link) {
	printf("<li><a href=\"%s\" %s>%s</a></li>\n", $link['href'], $link['attr'], $name);
    }
    echo "</ul>\n";
    // echo "</div>\n";
    
    // echo "</div>\n";
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
