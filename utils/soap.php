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
// A utility that generates PHP classes from SOAP types defined
// in the WSDL file.
// 

//
// The script should only be run in CLI mode.
//
if(isset($_SERVER['SERVER_ADDR'])) {
    die("This script should be runned in CLI mode.\n");
}

ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache 

$wsdl = "../source/ws/wsdl/batchelor.wsdl";
if(!file_exists($wsdl)) {
    die("(-) error: the WSDL file $wsdl don't exist\n");
}

$soap = &new SoapClient("../source/ws/wsdl/batchelor.wsdl");
if(!$soap) {
    die("(-) error: failed create soap client\n");
}

// 
// Generate classes:
// 
foreach($soap->__getTypes() as $type) {
    $lines = split("\n", $type);
    $class = "";
    $param = array();
    foreach($lines as $line) {
	$parts = explode(" ", $line);
	if($parts[0] == "struct") {
	    $class = ucfirst($parts[1]);
	    if(preg_match("/^[a-z]*$/", $parts[1])) {
		$class .= "Params";
	    }
	} elseif($parts[0] == null) {
	    $name = rtrim($parts[2], ";");
	    $type = $parts[1];
	    $param[$name] = $type;
	} elseif($parts[0] == "}") {
	    // 
	    // Generate the class:
	    // 
	    printf("//\n");
	    printf("// Synopsis: %s(%s)\n", $class, implode(", ", array_values($param)));
	    printf("//\n");
	    printf("class %s {\n", $class);
	    foreach($param as $name => $type) {
		printf("    var \$%s;\t// %s\n", $name, $type);
	    }
	    if(count($param)) {
		printf("    function %s($%s) {\n", $class, implode(", $", array_keys($param)));
	    } else {
		printf("    function %s() {\n", $class);
	    }
	    foreach($param as $name => $type) {
		printf("        \$this->%s = \$%s;\n", $name, $name);
	    }
	    printf("    }\n");
	    printf("}\n\n");
	} else {
	    die(sprintf("(-) error: unexpected part '%s'\n", $parts[0]));
	}
    }
}

?>
