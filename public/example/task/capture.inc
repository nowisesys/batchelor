<?php

use Batchelor\Logging\Target\File;
use Batchelor\Logging\Target\Memory;
use Batchelor\Queue\Task\Execute\Capture;
use Batchelor\Queue\Task\Execute\Runnable;

// 
// Capture command output in memory logger:
// 
$logging = new Memory();
$capture = Capture::create($logging, "ls -l /tmp");
$capture->execute();

printf("[C]: %s\n", $capture->getCommand());
foreach ($logging->getMessages() as $message) {
        printf("[L]: $message\n");
}

// 
// Capture command output in file logger:
// 
$logging = new File("/tmp/capture1.log");
$capture = Capture::create($logging, "ls -l /tmp");
$capture->execute();

// 
// Capture command output in file logger:
// 
$logging = new File("/tmp/capture2.log");
$capture = new Capture(new Runnable("ls -l /tmp"), $logging);
$capture->execute();

// 
// Show object structure:
// 
printf("[O]: %s\n", print_r($capture, true));
