<?php

use Batchelor\Logging\Target\File;

// 
// Output should be false for discarded messages.
// 
$logger = new File("/tmp/batchelor.log");
printf("[1] => [%b]?\n", $logger->debug("[T] This message should appear"));

// 
// Test discard debug messages:
// 
$logger->setThreshold(LOG_DEBUG);
printf("[2] => [%b]?\n", $logger->debug("[T] This message should be discarded"));
printf("[3] => [%b]?\n", $logger->info("[T] This message should appear"));

// 
// Test discard info or lower importance messages:
// 
$logger->setThreshold(LOG_INFO);
printf("[4] => [%b]?\n", $logger->debug("[T] This message should be discarded"));
printf("[5] => [%b]?\n", $logger->info("[T] This message should be discarded"));
printf("[6] => [%b]?\n", $logger->notice("[T] This message should appear"));

// 
// This should discard all messages:
// 
$logger->setThreshold(LOG_EMERG);
printf("[7] => [%b]?\n", $logger->emergency("[T] This message should be discarded"));

// 
// Reset threshold (any value > LOG_DEBUG will do):
// 
$logger->setThreshold(LOG_DEBUG + 1);
printf("[8] => [%b]?\n", $logger->debug("[T] This message should appear"));
