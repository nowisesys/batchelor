<?php

use Batchelor\Queue\System\SystemQueue;
use Batchelor\System\Service\Hostid;
use Batchelor\WebService\Types\JobData;
use Batchelor\WebService\Types\JobIdentity;

// 
// Test job status.
// 

$queue = new SystemQueue();

// 
// Check missing job:
// 
try {
        $status = $queue->getStatus((new Hostid())->getValue(), new JobIdentity("missing", "bogus"));
        print_r($status);
} catch (Exception $exception) {
        printf("[E] Got expected exception %s (%s)\n", get_class($exception), $exception->getMessage());
}

// 
// Add new job:
// 
$data = $queue->addJob(
    (new Hostid())->getValue(), new JobData("hello world", "data")
);
$status = $queue->getStatus((new Hostid())->getValue(), $data->identity);

printf("[D] %s\n", json_encode($data, JSON_PRETTY_PRINT));
printf("[D] %s\n", print_r($data, true));

// 
// Remove added job:
// 
 $queue->removeJob((new Hostid())->getValue(), $data->identity);
