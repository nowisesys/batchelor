<?php

use Batchelor\Logging\Format\Custom;
use Batchelor\Logging\Format\DateTime;
use Batchelor\Logging\Format\Export;
use Batchelor\Logging\Format\JsonEncode;
use Batchelor\Logging\Format\Serialize;
use Batchelor\Logging\Target\File;

$logger = new File("/tmp/batchelor.log");
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
$logger->message(LOG_NOTICE, "\nthis message should be trimmed\n\n");

// 
// Create another logger that logs identity also.
// 
$logger = new File("/tmp/batchelor.log", "batchelor");

// 
// Test using datetime formatting:
// 
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

// 
// Test custom formatter:
// 
$format = new Custom();
$logger->setFormat($format);
$logger->info("testing7");
$format->setExpand("@datetime@ <@priority@><@pid@> @message@ <@ident@>");
$logger->info("testing8");

// 
// Test JSON encoder:
// 
$format = new JsonEncode();
$logger->setFormat($format);
$logger->info("testing9");
$format->setCompact();
$logger->info("testing10");

// 
// Test PHP serialize:
// 
$format = new Serialize();
$logger->setFormat($format);
$logger->info("testing11");
$format->setCompact();
$logger->info("testing12");

// 
// Test PHP export:
// 
$format = new Export();
$logger->setFormat($format);
$logger->info("testing13");
$format->setCompact();
$logger->info("testing14");
$format->addContext();
$logger->info("testing15");
