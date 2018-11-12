<?php

use Batchelor\Queue\Task\Execute\Worker;

// 
// Create worker with non-running command:
// 
$worker = Worker::create("ls -l /tmp");

printf("[S]: %s\n", print_r($worker->getSelectable(), true));

printf("[W]: %b\n", $worker->isOpened());
$worker->open();
printf("[W]: %b\n", $worker->isOpened());

// 
// Read output of opened command:
// 
$process = $worker->getProcess();

if (($stream = $process->getStream(1))) {
        while ($process->isReadable(1)) {
                printf("[L]: %s\n", trim(fgets($stream)));
        }
}

// 
//  See exit code: Closing worker should close its process.
// 
printf("[P]: %s\n", print_r($process, true));
$worker->close();
printf("[P]: %s\n", print_r($process, true));

