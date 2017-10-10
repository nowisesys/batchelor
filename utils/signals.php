<?php

// -------------------------------------------------------------------------------
//  Copyright (C) 2007-2017 Anders Lövgren
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

include "../include/getopt.inc";

//
// The script should only be run in CLI mode.
//
if (isset($_SERVER['SERVER_ADDR'])) {
        die("This script should be runned in CLI mode.\n");
}

function usage($prog)
{
        printf("%s - list signal values from systems C header.\n", $prog);
        print "\n";
        printf("Usage: php %s --os=name [-v] [-d] [-h] [-V]\n", $prog);
        print "Options:\n";
        print "    --os=name:      Define the system OS (defaults to linux).\n";
        print "    -d,--debug:     Enable debug.\n";
        print "    -v,--verbose:   Be more verbose.\n";
        print "    -h,--help:      This help or use sect={example,timespec}.\n";
        print "    -V,--version:   Show version info.\n";
        printf("\n");
        printf("Possible values for --os=name are: linux, bsd, sunos\n");
}

//
// Show verison info.
//
function version($prog, $vers)
{
        printf("%s - list signal values (%s)\n", $prog, $vers);
}

// 
// Check $val argument for option $key.
// 
function check_arg($key, $val, $required, $prog)
{
        if ($required) {
                if (!isset($val)) {
                        die(sprintf("%s: option '%s' requires an argument\n", $prog, $key));
                }
        } else {
                if (isset($val)) {
                        die(sprintf("%s: option '%s' do not take an argument\n", $prog, $key));
                }
        }
}

//
// Parse command line options.
//
function parse_options(&$argv, $argc, &$options)
{
        //
        // Get command line options.
        //
        
        $args = array();
        get_opt($argv, $argc, $args);
        foreach ($args as $key => $val) {
                switch ($key) {
                        case "-d":
                        case "--debug":           // Enable debug.
                                check_arg($key, $val, false, $options->prog);
                                $options->debug = true;
                                break;
                        case "-v":
                        case "--verbose":         // Be more verbose.
                                check_arg($key, $val, false, $options->prog);
                                $options->verbose ++;
                                break;
                        case "-h":
                        case "--help":            // Show help.
                                usage($options->prog, $val);
                                exit(0);
                        case "-V":
                        case "--version":         // Show version info.
                                check_arg($key, $val, false, $options->prog);
                                version($options->prog, $options->version);
                                exit(0);
                        case "--os":
                                check_arg($key, $val, true, $options->prog);
                                $options->os = $val;
                                break;
                        default:
                                die(sprintf("%s: unknown option '%s', see --help\n", $options->prog, $key));
                }
        }
}

// 
// Get header path.
// 
function find_header($options)
{
        switch ($options->os) {
                case "linux":
                        if (file_exists("/usr/include/asm-x86_64/")) {
                                $header = "/usr/include/asm-x86_64/signal.h";
                        } else {
                                $header = "/usr/include/asm/signal.h";
                        }
                        break;
                case "bsd":
                        $header = "/usr/include/sys/signal.h";
                        break;
                case "sunos":
                        $header = "/usr/include/sys/iso/signal_iso.h";
                        break;
                default:
                        die(sprintf("%s: the operating system %s is not supported\n", $options->prog, $options->os));
                        break;
        }

        if (!file_exists($header)) {
                die(sprintf("%s: the signal C header %s do not exists\n", $options->prog, $header));
        }

        return $header;
}

function extract_signal_values($prog, $header)
{
        // 
        // Read entries on form '#define SIGKILL 9':
        // 
        $fp = popen("egrep '^#define[[:space:]]SIG' $header", "r");
        if (!$fp) {
                die(sprintf("%s: failed run grep on $header\n", $prog));
        }
        while ($str = fgets($fp)) {
                $match = array();
                if (preg_match('/^#define\s+(SIG[A-Z0-9]+)\s+(\d+)/', $str, $match)) {
                        printf("define (\"%s\", %d);\n", $match[1], $match[2]);
                }
        }
        pclose($fp);
}

function main($argc, &$argv)
{
        $prog = basename(array_shift($argv));
        $vers = trim(file_get_contents("../VERSION"));

        //
        // Setup defaults in options array:
        //
        $options = (object) array(
                    "os"      => "linux",
                    "debug"   => false,
                    "verbose" => 0,
                    "prog"    => $prog,
                    "version" => $vers
        );

        //
        // Fill $options with command line options.
        //
        parse_options($argv, $argc, $options);

        //
        // Dump options:
        //
        if ($options->debug) {
                var_dump($options);
        }

        $header = find_header($options);
        extract_signal_values($options->prog, $header);
}

//
// Start normal script execution.
//
main($_SERVER['argc'], $_SERVER['argv']);

?>
