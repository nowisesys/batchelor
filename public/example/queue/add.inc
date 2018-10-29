<?php

use Batchelor\Queue\System\SystemQueue;
use Batchelor\System\Service\Hostid;
use Batchelor\WebService\Types\JobData;

// 
// Test adding job in system queue:
// 

$queue = new SystemQueue();
$data = $queue->addJob(
    (new Hostid())->getValue(), new JobData("hello world", "data")
);

printf("%s\n", json_encode($data, JSON_PRETTY_PRINT));
print_r($data);
