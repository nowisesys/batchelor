<?php

// 
// When using cookies storage, this example should generate HTTP headers 
// like this:
// 
//        (1) Set-Cookie: hostid=8ad8757baa8564dc136c1e07507f4a98
//        (2) Set-Cookie: hostid=deleted; expires=Thu, 01-Jan-1970 00:00:01 GMT; Max-Age=0
//        (2) Set-Cookie: hostid=test2
// 

use Batchelor\System\Service\Hostid;

// 
// Get hostid from i.e. cookies:
// 
$service = new Hostid();
printf("VALUE: %s\n", $service->getValue());

// 
// Set value in request (not persisted):
// 
$service->setValue('test1');
printf("VALUE: %s\n", $service->getValue());

// 
// Pass argument for constructor:
// 
$service = new Hostid('test2');
printf("VALUE: %s\n", $service->getValue());

// 
// Switch queue (will change value permament):
// 
$service = new Hostid();
printf("VALUE: %s\n", $service->getValue());
$service->setQueue('test3');
printf("VALUE: %s\n", $service->getValue());

// 
// Reset to default queue (clear persisted):
// 
$service->setQueue(null);
printf("VALUE: %s\n", $service->getValue());
