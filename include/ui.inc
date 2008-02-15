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
    if(isset($GLOBALS['refresh'])) {
	printf("<meta http-equiv=\"refresh\" content=\"%d\" />", PAGE_REFRESH_RATE);
    }
}

// 
// Print left-side menu. Add additional links to the array.
// 
function print_menu()
{
    $links = array( "Submit"    => "index.php",
		    "Job Queue" => "index.php" );
    
    echo "<ul>\n";
    foreach($links as $name => $link) {
	printf("<li><a href=\"%s\">%s</a></li>\n", $link, $name);
    }
    echo "</ul>\n";
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
