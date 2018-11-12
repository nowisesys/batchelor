<?php

use Batchelor\Storage\Directory;

// 
// Test directory operations.
// 

$directory = new Directory("/tmp/batchelor");

// 
// Test the exists method:
// 
printf("Directory %s already exists: %b\n", $directory->getRealPath(), $directory->exists());

// 
// Create the main directory:
// 
printf("Created directory %s\n", $directory->create()->getRealPath());

// 
// Test open method:
// 
$success = $directory->open();
printf("Successful opened %s\n", $success->getPathname());

try {
        $missing = sprintf("/tmp/%s", md5(time()));
        $directory->open($missing);
} catch (Exception $exception) {
        printf("Expected failure open %s (%s: %s)\n", $missing, get_class($exception), $exception->getMessage());
}

// 
// Create sub directories relative to directory:
// 
$topdir1 = $directory->create("dir1");
$topdir2 = $directory->create("/tmp/batchelor/dir2");

$subdir1 = $topdir1->create("subdir1");
$subdir2 = $topdir2->create("subdir2/another");

$subdir3 = $subdir1->create("subdir3");
$subdir4 = $subdir2->create("subdir4", 0750);

// 
// Test file listing:
// 
printf("Using scan() on directory:\n");
$files = $directory->scan();
print_r($files);

printf("Using read() on directory:\n");
$files = $directory->read();
print_r($files);

printf("Using read() on topdir1 (listing relative to topdir1):\n");
$files = $topdir1->read();
print_r($files);

// 
// Test cleanup and delete:
// 
printf("Before topdir1->cleanup():\n");
$files = $directory->read();
print_r($files);

$topdir1->cleanup();

printf("After topdir1->cleanup():\n");
$files = $directory->read();
print_r($files);

$topdir1->delete();

printf("After topdir1->delete():\n");
$files = $directory->read();
print_r($files);

$topdir2->delete();

printf("After topdir2->delete():\n");
$files = $directory->read();
print_r($files);

// 
// Remove test directory:
// 
$directory->delete();
