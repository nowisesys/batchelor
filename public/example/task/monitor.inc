<?php

use Batchelor\Queue\Task\Execute\Monitor;
use Batchelor\Queue\Task\Execute\Runnable;
use Batchelor\Queue\Task\Execute\Selectable;

// 
// Run command under monitor.
// 
$monitor = new Monitor(new class extends Runnable implements Selectable {

            public function getCommand(): string
            {
                    return "ls -lR";
            }

            public function getDirectory()
            {
                    return "/tmp";
            }

            public function onError($stream)
            {
                    printf("[E]: Called\n");
                    while (($buff = trim(fgets($stream)))) {
                            printf("[E]: %s\n", $buff);
                    }
            }

            public function onOutput($stream)
            {
                    printf("[O]: Called\n");
                    while (($buff = trim(fgets($stream)))) {
                            printf("[O]: %s\n", $buff);
                    }
            }
    });

$monitor->execute();
