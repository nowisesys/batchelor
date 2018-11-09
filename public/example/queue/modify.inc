<?php

use Batchelor\Queue\System\SystemQueue;
use Batchelor\System\Service\Hostid;
use Batchelor\WebService\Types\JobData;

// 
// Insert task processor to supoort example.
// 
include("setup.inc");

// 
// Test add, remove and check job.
// 

$queue = new SystemQueue();
$hostid = (new Hostid())->getValue();

// 
// Test adding new job:
// 
$data1 = $queue->addJob($hostid, new JobData("hello world", "data"));
printf("[A] %s\n", json_encode($data1, JSON_PRETTY_PRINT));
printf("[A] %s\n", print_r($data1, true));

// 
// Check status of existing job:
// 
$data2 = $queue->getStatus($hostid, $data1->identity);
printf("[S] %s\n", json_encode($data2, JSON_PRETTY_PRINT));
printf("[S] %s\n", print_r($data2, true));

// 
// Test remove existing job:
// 
$data3 = $queue->removeJob($hostid, $data1->identity);
printf("[R] %s\n", json_encode($data3, JSON_PRETTY_PRINT));
printf("[R] %s\n", print_r($data3, true));

// 
// Check exception are thrown:
// 
try {
        $queue->getStatus($hostid, $data1->identity);
} catch (Exception $exception) {
        printf("[E] Got expected exception %s (%s)\n", get_class($exception), $exception->getMessage());
}

try {
        $queue->removeJob($hostid, $data1->identity);
} catch (Exception $exception) {
        printf("[E] Got expected exception %s (%s)\n", get_class($exception), $exception->getMessage());
}

