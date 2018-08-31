<?php

use Batchelor\Storage\Directory;

function print_path($path, $directory)
{
        printf("Input path for constructor: '%s'\n", $path);
        printf("      Path: %s\n", $directory->getPath());
        printf("   Dirname: %s\n", $directory->getDirname());
        printf("  Basename: %s\n", $directory->getBasename());
        printf("  Filename: %s\n", $directory->getFilename());
        printf("  Pathname: %s\n", $directory->getPathname());
        printf("  RealPath: %s\n", $directory->getRealPath());
}

foreach ([
    "/var/data/batchelor",
    "/tmp",
    "/tmp/subdir",
    "//tmp",
    "/tmp/file.txt",
    "/var/tmp",
    "/var/tmp/file.txt",
    ".",
    "..",
    "../..",
    "../../..",
    "../../../",
    "../../..//",
    "../../../.",
    "./file.txt",
    "..\\\..",
    "../..."
] as $path) {
        print_path($path, new Directory($path));
}
