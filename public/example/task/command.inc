<?php

use Batchelor\Queue\Task\Execute\Command;

// 
// Test command in blocking mode:
// 
$command = new Command("ls -l /tmp");
$command->setBlocking(1, true);

printf("** Listing in blocking mode: **\n");
while (($buff = $command->getOutput())) {
        printf("[B]: %s\n", trim($buff));
}
while (($buff = $command->getError())) {
        printf("[E]: %s\n", trim($buff));
}

// 
// Test command in non-blocking mode:
// 
$command = new Command("ls -l /tmp");
$command->setBlocking(1, false);

printf("** Listing in non-blocking mode: **\n");
while (!$command->isFinished() && $command->hasOutput(2)) {
        printf("[N]: %s\n", trim($command->getOutput()));
}
