<?php

use Batchelor\System\Service;
use Batchelor\System\Service\Hostid;

// 
// This array contains entries identified by service name whos value is
// either an service wrapper or callable:
// 
// [
//      'queue' => service|callable
// ]
// 
// Entries can also be object instances. This is useful for services that
// should always be instantiated (i.e. a session service).
// 
// [
//      'qeuue' => new Qeuue()
// ]
// 
// Example on using callable, in this case an closure. The name will be the
// requested service.
// 
// [
//      'queue' => function($name) {
//              $queue = new Queue();
//              $qeuue->connect('127.0.0.1');
//              return $queue;
//         }
// 

return [
        // 
        // The hostid service providing job queue mapping:
        // 
        'hostid' => new Service(Hostid::class)
];
