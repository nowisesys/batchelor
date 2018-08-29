<?php

// 
// Test directory iterator filter.
// 

use Batchelor\System\Directory\Filter\ArrayFilterIterator;
use Batchelor\System\Directory\Filter\RegexFilterIterator;

// 
// Filter using filenames:
// 
$iterator = new ArrayFilterIterator(
    new DirectoryIterator("."), ['index.php', 'index.inc']
);

printf("(i) Result (array filter):\n");
foreach ($iterator as $fileinfo) {
        printf("%s\n", $fileinfo->getFilename());
}

// 
// Filter using regex:
// 
$iterator = new RegexFilterIterator(
    new DirectoryIterator("."), '/index\..*/'
);

printf("(i) Result (regex filter):\n");
foreach ($iterator as $fileinfo) {
        printf("%s\n", $fileinfo->getFilename());
}

// 
// Filter recursive iterator:
// 

$iterator = new RegexFilterIterator(
    new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator("..")
    ), '/index\..*/'
);

printf("(i) Result (recursive + index files):\n");
foreach ($iterator as $fileinfo) {
        printf("%s/%s\n", $fileinfo->getPath(), $fileinfo->getFilename());
}

$iterator = new RegexFilterIterator(
    new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator("..")
    ), '/.*\.inc$/'
);

printf("(i) Result (recursive + *.inc):\n");
foreach ($iterator as $fileinfo) {
        printf("%s/%s\n", $fileinfo->getPath(), $fileinfo->getFilename());
}
