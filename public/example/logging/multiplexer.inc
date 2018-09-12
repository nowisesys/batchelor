<?php

use Batchelor\Logging\Special\Multiplexer;
use Batchelor\Logging\Target\File;
use Batchelor\Logging\Target\Syslog;

// 
// Add two different writers to multiplexer:
// 
$multiplexer = new Multiplexer();
$multiplexer->addWriter(new File("/tmp/batchelor.log", "batchelor"));
$multiplexer->addWriter(new Syslog("batchelor"), [LOG_ERR, LOG_WARNING]);

// 
// Check number of writers per priority:
// 
$priority = LOG_NOTICE;
printf("Targets %d: %d\n", $priority, count($multiplexer->getWriters($priority)));
$priority = LOG_WARNING;
printf("Targets %d: %d\n", $priority, count($multiplexer->getWriters($priority)));

// 
// Please inspect in output:
// 
print_r($multiplexer);

// 
// Test logging to multiplexer:
// 
$multiplexer->notice("Logged only to file target");
$multiplexer->warning("Logged to syslog and file targets");

// 
// Check that same object is used for multiple priorities:
// 
$file1 = $multiplexer->getWriters(4)[0];
$file2 = $multiplexer->getWriters(5)[0];

$hash1 = spl_object_hash($file1);
$hash2 = spl_object_hash($file2);

if (($file1 !== $file2) || ($hash1 != $hash2)) {
        throw new DomainException("The file writer objects are not referencing same object");
}
