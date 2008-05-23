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
// This script is tailored at Linux by default.
// 

function usage($prog)
{
    printf("php -d os=name %s\n", $prog);
    printf("example: php -d os=linux $prog\n");
    printf("possible values for name are: linux, bsd, sunos\n");
}

$prog = basename($argv[0]);
$header = "/usr/include/asm/signal.h";

switch(ini_get("os")) {
 case "linux":
    $header = "/usr/include/asm/signal.h";
    break;
 case "bsd":
    $header = "/usr/include/sys/signal.h";
    break;
 case "sunos":
    $header = "/usr/include/sys/iso/signal_iso.h";
    break;
 default:
}

if(!file_exists($header)) {
    usage($prog);
    exit(1);
}

// 
// Read entries on form '#define SIGKILL 9':
// 
$fp = popen("egrep '^#define[[:space:]]SIG' $header", "r");
if(!$fp) {
    die("failed run grep on $header\n");
}
while($str = fgets($fp)) {
    $match = array();
    if(preg_match('/^#define\s+(SIG[A-Z0-9]+)\s+(\d+)/', $str, $match)) {
	printf("define (\"%s\", %d);\n", $match[1], $match[2]);
    }
}
pclose($fp);

?>
