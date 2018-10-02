<?php

// 
// Test deamonizer class (must be run from CLI).
// 

require_once __DIR__ . '/../../../vendor/autoload.php';

use Batchelor\Logging\Target\File;
use Batchelor\System\Process\Daemonize;

$logger = new File("/tmp/daemonizer.log");

try {
        $daemonizer = new Daemonize();
        $daemonizer->perform();
} catch (RuntimeException $exception) {
        $logger->critical(print_r($exception, true));
}

for ($i = 1; $i <= 10; ++$i) {
        $logger->info("Hello world: %d", [$i]);
        sleep(1);
}
