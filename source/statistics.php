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
// The statistics module.
// 

// 
// Include configuration and libs.
// 
include "../conf/config.inc";
include "../include/common.inc";
include "../include/ui.inc";
include "../include/statistics.inc";

function print_body()
{
    printf("<h2><img src=\"icons/nuvola/statistics.png\"> Statistics</h2>\n");
}

function print_html($what)
{
    switch($what) {
     case "body":
	print_body();
	break;
     case "title":
	print "Batchelor - Statistics";
	break;
     default:
	print_common_html($what);
	break;
    }
}

include "../template/standard.ui";

?>
