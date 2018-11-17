<?php

use Batchelor\Logging\Format\JsonEncode;
use Batchelor\Logging\Target\Callback;

// 
// Use false as seconds argument to receive unformatted messages in callback.
// 
$logger = new Callback(static function(int $priority, string $message) {
        printf("%d: %s\n", $priority, $message);
}, true);

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
// Test JSON encoder:
// 
$format = new JsonEncode();
$logger->setFormat($format);
$logger->info("Testing9");
$format->setCompact();
$logger->info("Testing10");
