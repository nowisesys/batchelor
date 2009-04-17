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
// This script serves XML Schemas for the REST service. The request URI is
// either 'index.php?schema' (default) or 'index.php?xlink'.
// 

$files = array( "schema" => "batchelor-rest-schema.xsd",
		"xlink"  => "batchelor-rest-xlink.xsd" );

if(isset($_REQUEST['schema'])) {
    readfile($files['schema']);
} elseif(isset($_REQUEST['xlink'])) {
    readfile($files['xlink']);    
} else {
    readfile($files['schema']);
}

?>
