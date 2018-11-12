<?php

// 
// Test using ASCII tree iterator.
// 

use Batchelor\Storage\Directory;

$iter = new RecursiveDirectoryIterator("../..", FilesystemIterator::SKIP_DOTS);
$tree = new RecursiveTreeIterator($iter);

printf("Dump(1):\n");
print_r($iter);
print_r($tree);

$dirs = new Directory("../..", FilesystemIterator::SKIP_DOTS);
$iter = $dirs->getIterator();
$tree = new RecursiveTreeIterator($iter);

printf("Dump(2):\n");   // This dump should be equivalent to dump(1):
print_r($iter);
print_r($tree);

// 
// Display ASCII directory tree:
// 
printf("%s ->\n", $dirs->getPathname());
foreach ($tree as $node) {
        printf("  %s\n", $node);
}
