<?php

// 
// Test directory scanner.
// 


use Batchelor\System\Directory\Scanner;
use Batchelor\System\Directory\ScannerResult;

// 
// List all files in current directory:
// 
$scanner = new Scanner(".");
$files = $scanner->getFiles();

echo "(i) Current directory:\n";
foreach ($files as $file) {
        printf("%s\n", $file);
}

// 
// List all files in parent directory:
// 
$scanner = new Scanner("..");
$files = $scanner->getFiles();

echo "(i) Parent directory:\n";
foreach ($files as $file) {
        printf("%s\n", $file);
}

// 
// Filter files from parent directory:
// 
$scanner = new Scanner("..");
$scanner->setOptions(Scanner::SKIP_DOTS);
$files = $scanner->getFiles();

echo "(i) Parent directory (filtered::skip(dots)):\n";
foreach ($files as $file) {
        printf("%s\n", $file);
}

// 
// Filter files from parent directory:
// 
$scanner = new Scanner("..");
$scanner->setOptions(Scanner::SKIP_DIRS);
$files = $scanner->getFiles();

echo "(i) Parent directory (filtered::skip(dirs)):\n";
foreach ($files as $file) {
        printf("%s\n", $file);
}

// 
// Filter files from current directory:
// 
$scanner = new Scanner(".");
$scanner->setFilter(['filter.inc', 'filter.php']);
$files = $scanner->getFiles();
//
echo "(i) Current directory (filtered::array):\n";
foreach ($files as $file) {
        printf("%s\n", $file);
}

// 
// Filter files from current directory:
// 
$scanner = new Scanner(".");
$scanner->setFilter('/filter\..*/');
$files = $scanner->getFiles();
//
echo "(i) Current directory (filtered::regex):\n";
foreach ($files as $file) {
        printf("%s\n", $file);
}

// 
// Filter files from parent directory (recursive):
// 
$scanner = new Scanner("..");
$scanner->setFilter(['index.inc', 'index.php']);
$scanner->setRecursive();
$files = $scanner->getFiles();

echo "(i) Parent directory (filtered::array + recursive):\n";
foreach ($files as $file) {
        printf("%s\n", $file);
}

// 
// Initialize by options array:
// 
$scanner = new Scanner("..", [
        'recursive' => true,
        'filter'    => ['index.inc', 'index.php'],
        'options'   => Scanner::SKIP_DOTS | Scanner::SKIP_EMPTY
    ]);
$files = $scanner->getFiles();

echo "(i) Parent directory (using options array):\n";
foreach ($files as $file) {
        printf("%s\n", $file);
}

// 
// Initialize when calling function:
// 
$scanner = new Scanner("..");
$files = $scanner->getFiles(Scanner::SKIP_DOTS);

echo "(i) Parent directory (using function argument):\n";
foreach ($files as $file) {
        printf("%s\n", $file);
}

// 
// Use scanner as an iterator:
// 
$scanner = new Scanner("..");
$scanner->setRecursive();
$scanner->setOptions(Scanner::SKIP_DOTS | Scanner::SKIP_DIRS);

echo "(i) Use scanner as an iterator:\n";
foreach ($scanner as $file) {
        printf("%s\n", $file);
}

// 
// Use scanner as an iterator:
// 
$scanner = new Scanner("..");
$scanner->setRecursive();

echo "(i) Use SPL fileinfo objects:\n";
foreach ($scanner as $file) {
        if ($file->isFile()) {
                printf("%s\n", $file->getRealpath());
        }
}

// 
// Use scanner result iterator for formatting:
// 
$scanner = new Scanner("..");
$scanner->setOptions(Scanner::SKIP_DOTS);
$result = new ScannerResult($scanner, Scanner::FILENAME_ANCHORED);

echo "(i) Use scanner result formatter:\n";
foreach ($result as $file) {
        printf("%s\n", $file);
}

// 
// Use member methods for define output format. Call getResult() to get
// the formatter iterator object.
// 
$scanner = new Scanner("..");
$scanner->setRecursive();
$scanner->setOptions(Scanner::SKIP_DOTS);
$scanner->setFormat(Scanner::FILENAME_ANCHORED);
$result = $scanner->getResult();

echo "(i) Use scanner result formatter:\n";
foreach ($result as $file) {
        printf("%s\n", $file);
}

// 
// Use a custom formatter for creating MD5 index for files:
// 
$scanner = new Scanner("..");
$scanner->setRecursive();
$scanner->setOptions(Scanner::SKIP_DOTS | Scanner::SKIP_DIRS);

$result = $scanner->getResult();
$result->setCallback(function($fileinfo) {
        $real = $fileinfo->getRealpath();
        $full = sprintf("%s/%s", $fileinfo->getPath(), $fileinfo->getFilename());
        return sprintf("%s\t%s", md5_file($real), trim($full, "./"));
});

echo "(i) Use custom result formatter:\n";
foreach ($result as $file) {
        printf("%s\n", $file);
}
