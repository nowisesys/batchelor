<?php

// 
// Test use iterator.
// 

use Batchelor\System\Directory;

// 
// Create iterator only returning leavs (filenames):
// 
$iterator = new RecursiveIteratorIterator(
    new Directory("../.."), RecursiveIteratorIterator::LEAVES_ONLY
);

// 
// The directory object is accessable during iteration because
// entries uses the custom file class:
// 
foreach ($iterator as $entry) {
        printf("%s\n\t%s (%s)\n\t%s (%s)\n\t(%s)\n\n", $entry->getBasename(), $entry, get_class($entry), $entry->getDirectory(), get_class($entry->getDirectory()), get_class($iterator->getInnerIterator()));
}
