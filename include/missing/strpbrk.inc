<?php

// -------------------------------------------------------------------------------
//  Copyright (C) 2009 Anders Lövgren
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
// A replacement for missing strpbrk() in PHP < 5
// 

// 
// The strpbrk() function locates the first occurrence in the string haystack
// of any of the characters in the string accept. 
// 
// The function prototype are:
// string strpbrk(string haystack, string accept)
// 
function strpbrk($haystack, $accept)
{
    for($i = 0; $i < strlen($haystack); ++$i) {
	for($j = 0; $j < strlen($accept); ++$j) {
	    if($haystack[$i] == $accept[$j]) {
		return substr($haystack, $i);
	    }
	}
    }
}

?>
