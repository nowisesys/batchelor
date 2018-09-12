<?php

use Batchelor\Logging\Format\Custom;
use Batchelor\Logging\Format\DateTime;
use Batchelor\Logging\Format\Dumper;
use Batchelor\Logging\Format\Export;
use Batchelor\Logging\Format\JsonEncode;
use Batchelor\Logging\Format\Serialize;
use Batchelor\Logging\Target\File;

$logger = new File("/tmp/batchelor.log");
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
$logger->info("Testing1");
$format->setFormat(DateTime::FORMAT_ISO_DATABASE);
$logger->info("Testing2");
$format->setFormat(DateTime::FORMAT_LOCALE);
$logger->info("Testing3");
$format->setFormat(DateTime::FORMAT_UNIX_EPOCH);
$logger->info("Testing4");

// 
// Test microseconds granularity:
// 
$format->setFormat(DateTime::FORMAT_MICROSEC);
$logger->info("Testing5");
$format->setFormat(DateTime::FORMAT_FLOATSEC);
$logger->info("Testing6");

// 
// Test custom formatter:
// 
$format = new Custom();
$logger->setFormat($format);
$logger->info("Testing7");
$format->setExpand("@datetime@ <@priority@><@pid@> @message@ <@ident@>");
$logger->info("Testing8");

// 
// Test JSON encoder:
// 
$format = new JsonEncode();
$logger->setFormat($format);
$logger->info("Testing9");
$format->setCompact();
$logger->info("Testing10");

// 
// Test PHP serialize:
// 
$format = new Serialize();
$logger->setFormat($format);
$logger->info("Testing11");
$format->setCompact();
$logger->info("Testing12");

// 
// Test PHP export:
// 
$format = new Export();
$logger->setFormat($format);
$logger->info("Testing13");
$format->setCompact();
$logger->info("Testing14");
$format->addContext();
$logger->info("Testing15");

// 
// Test PHP dump:
// 
$format = new Dumper();
$logger->setFormat($format);
$logger->info("Testing15");
$format->setCompact();
$logger->info("Testing16");
$format->addContext();
$logger->info("Testing17");
