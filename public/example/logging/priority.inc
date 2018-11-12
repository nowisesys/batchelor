<?php

use Batchelor\Logging\Special\Priority;

$logger = new Priority("/tmp/batchelor", "batchelor");
$message = $logger->getMessage("Hello world, %s!", ["anders"]);

printf("Test dump formatted message:\n");
print_r($message);

// 
// These should all end up in separate logs:
// 
$logger->emergency("Hello world!");
$logger->alert("Hello world!");
$logger->critical("Hello world!");
$logger->error("Hello world!");
$logger->warning("Hello world!");
$logger->notice("Hello world!");
$logger->info("Hello world!");
$logger->debug("Hello world!");

// 
// Test log using generic message method:
// 
$logger->message(LOG_NOTICE, "Hello world, %s!", ["anders"]);

// 
// Test combine log messages:
// 
$logger->setTarget(LOG_EMERG, "fatal");
$logger->setTarget(LOG_ALERT, "fatal");
$logger->setTarget(LOG_CRIT, "fatal");

$logger->emergency("Should be logger to fatal log");
$logger->critical("Should be logger to fatal log");
$logger->error("Should be logger to error log");

