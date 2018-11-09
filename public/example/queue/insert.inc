<?php

use Batchelor\Queue\System\SystemQueue;
use Batchelor\System\Service\Hostid;
use Batchelor\WebService\Types\JobData;

// 
// Insert task processor to supoort example.
// 
include("setup.inc");

// 
// Insert some jobs:
// 
$queue = new SystemQueue();
$hostid = (new Hostid())->getValue();

for ($i = 0; $i < 5; ++$i) {
        $data1 = $queue->addJob($hostid, new JobData("hello world", "data", "task$i", "Job name $i"));
        printf("[A] %s\n", json_encode($data1, JSON_PRETTY_PRINT));
        printf("[A] %s\n", print_r($data1, true));
}
