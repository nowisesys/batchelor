<?php

use Batchelor\Logging\Format\DateTime;
use Batchelor\Logging\Target\File;

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
$format = $logger->getFormat()->getDateTime();
$format->setFormat(DateTime::FORMAT_HUMAN);
$logger->info("testing1");
$format->setFormat(DateTime::FORMAT_ISO_DATABASE);
$logger->info("testing2");
$format->setFormat(DateTime::FORMAT_LOCALE);
$logger->info("testing3");
$format->setFormat(DateTime::FORMAT_UNIX_EPOCH);
$logger->info("testing4");

// 
// Test microseconds granularity:
// 
$format->setFormat(DateTime::FORMAT_MICROSEC);
$logger->info("testing5");
$format->setFormat(DateTime::FORMAT_FLOATSEC);
$logger->info("testing6");
