<?php

use Batchelor\Logging\Format\DateTime;
use Batchelor\Logging\Target\Memory;

$logger = new Memory();
$message = $logger->getMessage("Hello world, %s!", ["anders"]);

printf("Test dump formatted message:\n");
print_r($message);

// 
// Test log using various priorities:
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
// Dump all messages:
// 
print_r($logger->getMessages());

// 
// Use custom formatting of message and datetime:
// 
$logger = new Memory([
        'expand'   => "@datetime@ @message@ (@priority@)",
        'datetime' => DateTime::FORMAT_LOCALE
    ]);
$logger->notice("Using custom formatting!");
print_r($logger->getMessages());
