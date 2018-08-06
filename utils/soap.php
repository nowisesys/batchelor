<?php

// -------------------------------------------------------------------------------
//  Copyright (C) 2007-2009 Anders Lövgren
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
// This utility generates PHP classes matching the SOAP types defined
// by the WSDL read from a file or URL.
// 
//
// The script should only be run in CLI mode.
//
if (isset($_SERVER['SERVER_ADDR'])) {
        die("This script should be runned in CLI mode.\n");
}

ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache 
// 
// This function generates SOAP type classes.
// 

function generate_classes($wsdl)
{
        $soap = new SoapClient($wsdl);
        if (!$soap) {
                die("(-) error: failed create soap client\n");
        }

        // print_r($soap->__getTypes());
        // var_dump($soap->__getTypes());

        foreach ($soap->__getTypes() as $type) {
                $lines = explode("\n", $type);
                $class = "";
                $param = array();
                foreach ($lines as $line) {
                        $parts = explode(" ", $line);
                        if ($parts[0] == "struct") {
                                $class = ucfirst($parts[1]);
                                if (preg_match("/^[a-z]*$/", $parts[1])) {
                                        $class .= "Params";
                                }
                        } elseif ($parts[0] == null) {
                                $name = rtrim($parts[2], ";");
                                $type = $parts[1];
                                $param[$name] = $type;
                        } elseif ($parts[0] == "}") {
                                // 
                                // Generate the class:
                                // 
                                printf("//\n");
                                printf("// Synopsis: %s(%s)\n", $class, implode(", ", array_values($param)));
                                printf("//\n");
                                printf("class %s {\n", $class);
                                foreach ($param as $name => $type) {
                                        printf("    public \$%s;\t// %s\n", $name, $type);
                                }
                                if (count($param)) {
                                        printf("    public function __constructor($%s) {\n", implode(", $", array_keys($param)));
                                } else {
                                        printf("    public function __constructor() {\n");
                                }
                                foreach ($param as $name => $type) {
                                        printf("        \$this->%s = \$%s;\n", $name, $name);
                                }
                                printf("    }\n");
                                printf("}\n\n");
                        } else {
                                die(sprintf("(-) error: unexpected part '%s'\n", $parts[0]));
                        }
                }
        }
}

function usage($prog)
{
        $wsdl = array(
                "remote" => "http://localhost/batchelor/ws/schema/wsdl/?wsdl",
                "local"  => "../source/ws/schema/wsdl/batchelor.wsdl.cache"
        );

        printf("%s - generates PHP classes from SOAP types\n", $prog);
        printf("\n");
        printf("Usage: %s <wsdl-location-url>\n", $prog);
        printf("\n");
        printf("Note: The wsdl-location-url is either an local file or the URL\n");
        printf("to an WSDL location on an server.\n");
        printf("\n");
        foreach ($wsdl as $key => $url) {
                printf("Example (%s wsdl):\t%s %s\n", $key, $prog, $url);
        }
}

// 
// The main function.
// 
function main($argc, &$argv)
{
        $prog = basename($_SERVER['argv'][0]);

        if ($_SERVER['argc'] == 1) {
                usage($prog);
                exit(1);
        }

        switch ($_SERVER['argv'][1]) {
                case "-h":
                case "--help":
                        usage($prog);
                        exit(0);
                default:
                        generate_classes($_SERVER['argv'][1]);
                        break;
        }
}

main($_SERVER['argc'], $_SERVER['argv']);

