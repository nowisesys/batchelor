<?php

use Batchelor\Logging\File;

$logger = new File("/tmp/batchelor1.log");
$message = $logger->getMessage("hello world, %s!", ["anders"]);

printf("Test dump formatted message:\n");
print_r($message);

// 
// Test log using various priorities:
// 
$logger->emergency("hello world!");
$logger->alert("hello world!");
$logger->critical("hello world!");
$logger->error("hello world!");
$logger->warning("hello world!");
$logger->notice("hello world!");
$logger->info("hello world!");
$logger->debug("hello world!");

// 
// Test log using generic message method:
// 
$logger->message(LOG_NOTICE, "hello world, %s!", ["anders"]);

// 
// Test some more logging options, i.e. datetime formatting:
// 
$logger = new File("/tmp/batchelor2.log", "batchelor", LOG_PERROR | LOG_PID);
$logger->setFormat(File::DATETIME_HUMAN);
$logger->info("testing1");
$logger->setFormat(File::DATETIME_ISO_DATABASE);
$logger->info("testing2");
$logger->setFormat(File::DATETIME_LOCALE);
$logger->info("testing3");
$logger->setFormat(File::DATETIME_UNIX_EPOCH);
$logger->info("testing4");

// 
// Test microseconds granularity:
// 
$logger = new File("/tmp/batchelor3.log", "batchelor", LOG_PERROR | LOG_PID, File::GRANULARITY_FLOATSEC);
$logger->info("testing5");
$logger = new File("/tmp/batchelor3.log", "batchelor", LOG_PERROR | LOG_PID, File::GRANULARITY_MICROSEC);
$logger->info("testing6");
