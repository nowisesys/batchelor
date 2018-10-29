<?php

use Batchelor\Queue\System\SystemQueue;
use Batchelor\System\Service\Hostid;
use Batchelor\WebService\Types\QueueFilterResult;
use Batchelor\WebService\Types\QueueSortResult;

// 
// Get pending jobs:
// 
$queue = new SystemQueue();
$data = $queue->listJobs((new Hostid())->getValue(), QueueSortResult::JOBID(), QueueFilterResult::NONE());

printf("%s\n", json_encode($data, JSON_PRETTY_PRINT));
print_r($data);
