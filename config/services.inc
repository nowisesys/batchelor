<?php

use Application\Task\CombineTask;
use Application\Task\ReverseText;
use Application\Task\WordCounter;
use Batchelor\Queue\Task\Service as TaskService;
use Batchelor\System\Service;

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
//      'queue' => new Queue()
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
// ]
// 

return [
        // 
        // The hostid service providing job queue mapping.
        // 
        'hostid'      => new Service(Service\Hostid::class),
        // 
        // The persistance service for storing data in cookies or session.
        // 
        'persistance' => new Service(Service\Persistance::class),
        // 
        // The application config from defaults.app.
        // 
        'app'         => new Service(Service\Config::class),
        // 
        // The data directory storage service. 
        // 
        'data'        => new Service(Service\Storage::class),
        // 
        // The logger service.
        // 
        'logger'      => new Service(Service\Logging::class),
        // 
        // The job/work queue service.
        // 
        'queue'       => new Service(Service\Queue::class),
        // 
        // The system cache service.
        // 
        'cache'       => new Service(Service\Cache::class),
        // 
        // The security context service.
        // 
        'security'    => new Service(Service\Security::class),
        // 
        // The task processor service provides the repository of task runners 
        // used by the consumer of scheduled jobs (processor). Clear the list
        // and replace with real tasks defining your application.
        // 
        'processor'   => function() {
                $service = new TaskService([
                        'combine' => new CombineTask(),
                        'reverse' => new ReverseText(),
                        'counter' => new WordCounter()
                ]);
                $service->addAlias('combine', 'default');
                return $service;
        }
];
