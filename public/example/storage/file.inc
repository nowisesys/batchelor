<?php

use Batchelor\Storage\File;

// 
// Test file class.
// 

$file = new File("/tmp/test.txt");
printf("File exist: %b\n", $file->isFile());

// 
// Write text and read back:
// 
$file->putContent("some text");
printf("File exist: %b\n", $file->isFile());
printf("Content: '%s'\n", $file->getContent());

// 
// Test get parent directory:
// 
$parent = $file->getParent();
printf("Path: %s (%s)\n", $file->getRealPath(), $parent->getRealPath());

$parent = $file->getDirectory();
printf("Path: %s (%s)\n", $file->getRealPath(), $parent->getRealPath());

// 
// Show path information:
// 
$path = [
        'path' => $file->getPath(),
        'real' => $file->getRealPath(),
        'name' => $file->getPathname(),
        'dir'  => $file->getDirname(),
        'base' => $file->getBasename()
];
printf("Path: %s\n", print_r($file, true));
printf("Path: %s\n", print_r($path, true));

// 
// Check download (sendfile) support:
// 
printf("MIME type: %s\n", $file->getMimeType());
printf("Headers: %s\n", print_r($file->getDownloadHeaders(), true));

// 
// Show download button:
// 
printf("<a class=\"w3-btn\" href=\"sendfile\">Download</a>\n");
