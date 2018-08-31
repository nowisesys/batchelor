<?php

// 
// Test navigate directory.
// 


use Batchelor\Storage\Directory;

function read_directory($directory)
{
        printf("[ENTER] %s (%s)\n", $directory, get_class($directory));

        foreach ($directory as $entry) {
                if ($entry->isDir()) {
                        printf("[DIR]   %s (%s)\n", $entry, get_class($entry));
                        read_directory($entry->getDirectory()); // Navigate
                } else {
                        printf("[FILE]  %s (%s)\n", $entry, get_class($entry));
                }
        }
}

function read_parentdir($directory)
{
        $parentdir = $directory->getParent();
        
        printf("[PATH]: %s (%s) [C]\n", $directory->getPathname(), $directory->getRealpath());
        printf("[PATH]: %s (%s) [P]\n", $parentdir->getPathname(), $parentdir->getRealpath());
}

read_directory(new Directory("../.."));
read_parentdir(new Directory("../.."));
read_parentdir(new Directory("/tmp/batchelor/navigate"));
