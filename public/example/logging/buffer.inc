<?php

use Batchelor\Logging\Special\Buffer;
use Batchelor\Logging\Target\File;

$logger = new File("/tmp/batchelor.log", "batchelor");
$buffer = new Buffer($logger);
$message = $buffer->getMessage("Hello world, %s!", ["anders"]);

printf("Test dump formatted message:\n");
print_r($message);

// 
// Test log using various priorities:
// 
$buffer->emergency("Hello world!");
$buffer->alert("Hello world!");
$buffer->critical("Hello world!");
$buffer->error("Hello world!");
$buffer->warning("Hello world!");
$buffer->notice("Hello world!");
$buffer->info("Hello world!");
$buffer->debug("Hello world!");

// 
// Test log using generic message method:
// 
$buffer->message(LOG_NOTICE, "Hello world, %s!", ["anders"]);

// 
// Test flush buffered messages:
// 
printf("Before flush (empty=%b, size=%d):\n", $buffer->isEmpty(), $buffer->getSize());
print_r($buffer);
$buffer->flush();
printf("After flush (empty=%b, size=%d):\n", $buffer->isEmpty(), $buffer->getSize());
print_r($buffer);

// 
// Test flush by destructor:
// 
$buffer->info("This message should be logged at terminate");

// 
// Test buffer in buffer (for experiment only):
// 
$buffer = new Buffer($buffer);
$buffer->info("Test message from buffer in buffer");

// 
// Test get buffer method:
// 
$buffer = $logger->getBuffered();
printf("Initial (empty=%b, size=%d):\n", $buffer->isEmpty(), $buffer->getSize());
