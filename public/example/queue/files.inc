<?php

use Batchelor\Queue\System\SystemQueue;
use Batchelor\System\Service\Hostid;
use Batchelor\WebService\Types\QueueFilterResult;
use Batchelor\WebService\Types\QueueSortResult;

// 
// Filter queue on all finished jobs:
// 
$queue = new SystemQueue();
$hostid = (new Hostid())->getValue();

$data = $queue->listJobs($hostid, QueueSortResult::JOBID(), QueueFilterResult::SUCCESS());

// 
// Check job listing:
// 
if (count($data) == 0) {
        throw new RuntimeException("The job queue is empty");
}

// 
// Get files from first job:
// 
$first = current($data);
$files = $queue->getReader($hostid)->getFiles($first->identity);

printf("%s\n", json_encode($files, JSON_PRETTY_PRINT));
print_r($files);
