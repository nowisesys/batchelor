<?php

use Batchelor\Queue\Task\Execute\Runnable;
use Batchelor\Queue\Task\Execute\Selectable;
use Batchelor\Queue\Task\Execute\Spawner;

// 
// Use process spawner:
// 
$process = (new Spawner(new class extends Runnable implements Selectable {

            public function getCommand(): string
            {
                    return "ls -l /tmp";
            }
    }
    ))->open();

// 
// Read command output:
// 
if (($stream = $process->getStream(1))) {
        while ($process->isReadable(1)) {
                printf("[L]: %s\n", trim(fgets($stream)));
        }
}

// 
// Get process status:
// 
printf("[S]: %s\n", print_r(
        $process->getStatus(), true
));

// 
// Close and capture exist code:
// 
$process->close();
printf("[E]: %d\n", $process->getExitCode());
