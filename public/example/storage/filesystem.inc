<?php

use Batchelor\Storage\FileSystem;

// 
// Test file system class.
// 

$filesystem = new FileSystem();

// 
// Test exist method:
// 
printf("Exists: %b\n", $filesystem->exists());
printf("Exists: %b\n", $filesystem->exists("/tmp"));
printf("Exists: %b\n", $filesystem->exists("/missing"));

// 
// Test create method:
// 
$subdir1 = $filesystem->create("/tmp/batchelor/subdir1");
$subdir2 = $filesystem->create("tmp/batchelor/subdir2");        // Relative to '/'
$subdir3 = $subdir2->create("adam/bertil");

printf("Create: %s (%s)\n", $subdir1->getPathname(), $subdir1->getRealPath());
printf("Create: %s (%s)\n", $subdir2->getPathname(), $subdir2->getRealPath());

// 
// Test open directory:
// 
$directory = $filesystem->open("/tmp/batchelor/");
printf("Open: %s (%s)\n", $directory->getPathname(), $directory->getRealPath());
$directory = $filesystem->open("tmp/batchelor/");
printf("Open: %s (%s)\n", $directory->getPathname(), $directory->getRealPath());

// 
// Test cleanup and delete:
// 
printf("Before filesystem->cleanup(subdir2):\n");
$files = $directory->read();
print_r($files);

$filesystem->cleanup($subdir2);

printf("After filesystem->cleanup(subdir2):\n");
$files = $directory->read();
print_r($files);

$filesystem->delete($subdir1);
$filesystem->delete($subdir2);

printf("After filesystem->delete(subdir*):\n");
$files = $directory->read();
print_r($files);

// 
// Test iteration or root directories:
// 
printf("Sub directories of %s:\n", $filesystem->getPathname());
foreach ($filesystem as $dir) {
        printf("%s (%s)\n", $dir, get_class($dir));
}
