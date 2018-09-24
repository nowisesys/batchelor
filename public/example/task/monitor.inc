<?php

use Batchelor\Queue\Task\Execute\Monitor;
use Batchelor\Queue\Task\Execute\Runnable;
use Batchelor\Queue\Task\Execute\Selectable;

// 
// Run command under monitor.
// 
$monitor = new Monitor(new class extends Runnable implements Selectable {

            private $_files = 0;

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
                            ++$this->_files;
                            printf("[O]: %s\n", $buff);
                    }
            }

            public function getFilesCount()
            {
                    return $this->_files;
            }
    });

$monitor->execute();

// 
// Call method thru monitor.
// 
$monitor = new Monitor(new class extends Runnable implements Selectable {

            private $_files = 0;

            public function getCommand(): string
            {
                    return "find /tmp -type f";
            }

            public function onOutput($stream)
            {
                    while (($buff = trim(fgets($stream)))) {
                            ++$this->_files;
                    }
            }

            public function getFilesCount()
            {
                    return $this->_files;
            }
    });

$monitor->execute();

printf("[C]: %d\n", $monitor->getFilesCount());
