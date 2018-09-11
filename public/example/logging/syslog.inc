<?php

use Batchelor\Logging\Target\Syslog;

$logger = new Syslog("batchelor");
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
