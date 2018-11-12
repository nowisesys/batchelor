<?php

use Batchelor\Queue\System\SystemDirectory;
use Batchelor\System\Service\Hostid;

// 
// List job identities in system directory:
// 

$data = (new SystemDirectory(
    (new Hostid())->getValue()
    ))->getJobs();

printf("%s\n", json_encode($data, JSON_PRETTY_PRINT));
print_r($data);
