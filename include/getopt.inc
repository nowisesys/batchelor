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
// The PHP builtin getopt() function is a sad story. This is an enhanced 
// version that supports short and long options. It returns an key => val
// array of all options from $argv. The special case with two or more flags
// after a single '-' (i.e. -abc) is treated as -a -b -c.
// 
// This get_opt() will silently ignore -x y z and store them as:
// 
// Array
// (
//    [-x] => y
//    [z]  =>
// )
// 
// These kind of errors should be trapped by the default case when the
// result array is examined.
// 
// Example:
// 
// $args = array();
// get_opt($_SERVER['argv'], $_SERVER['argc'], $args);
// foreach($args as $key => $val) {
//     // 
//     // All accepted option should have an switch case.
//     // 
//     switch($key) {
//         case "-h":
//         case "--help":
//             usage();
//             exit(0);
//         // 
//         // Example of option don't take an argument.
//         // 
//         case "x":
//             if(isset($val)) {
//                 die("option '-x' don't take an argument.");
//             }
//             ...
//             break;
//         // 
//         // Example required argument missing.
//         // 
//         case "y":
//             if(!isset($val)) {
//                 die("option '-y' requires an argument.");
//             }
//             ...
//             break;
//         // 
//         // Example on optional argument.
//         // 
//         case "z":
//             if(isset($val)) {
//                 ...
//             }
//             else {
//                 ...
//             }
//             break;
//         default:      // Handles unknown options.
//             die(sprintf("unknown option '%s'", $key));
//     }
// }
//
// 
// Parse command line option. The parsed options is inserted
// in the opts array.
// 
function get_opt($argv, $argc, &$opts)
{
        $args = array();

        // 
        // Make sure we got any arguments.
        // 
        if ($argc == 0 || count($argv) == 0) {
                return;
        }

        //
        // Skip script name if set.
        //
    if ($_SERVER['SCRIPT_FILENAME'] == $argv[0]) {
                array_shift($argv);
        }

        // 
        // Resolve issue with short options on form -xyz
        // 
        foreach ($argv as $arg) {
                if (preg_match("/^\-\w{2,}$/", $arg)) {
                        // 
                        // Matched -xyz, split as -x -y -z
                        // 
                        $len = strlen($arg);
                        for ($i = 1; $i < $len; $i++) {
                                array_push($args, sprintf("-%s", $arg[$i]));
                        }
                } else {
                        // 
                        // Single -x, --xxx or option argument.
                        // 
                        array_push($args, $arg);
                }
        }

        // 
        // Split long options.
        // 
        $argv = $args;
        $args = array();
        foreach ($argv as $arg) {
                if (preg_match("/^\-\-.*$/", $arg)) {
                        // 
                        // Matched long option --xxx{=yyy}
                        // 
                        $pos = strpos($arg, "=");
                        if ($pos) {
                                $key = substr($arg, 0, $pos);
                                $val = substr($arg, $pos + 1);
                        } else {
                                $key = $arg;
                                $val = null;
                        }
                        array_push($args, $key);
                        if (isset($val)) {
                                array_push($args, $val);
                        }
                } else {
                        // 
                        // Simply copy from argv -> args.
                        // 
                        array_push($args, $arg);
                }
        }

        // 
        // Begin real parsing of command line options.
        // 
        while (true) {
                $key = array_shift($args);
                $val = array_shift($args);
                if (!$key) {
                        break;
                }
                if ($val[0] == '-') {
                        // 
                        // Push back.
                        // 
                        array_unshift($args, $val);
                        $val = null;
                }
                $opts[$key] = $val;
        }
}

?>
