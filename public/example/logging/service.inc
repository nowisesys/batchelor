<?php

use Batchelor\System\Service\Logging as Logger;

// 
// Test using logger service (initialized by system config):
// 
$logger = new Logger();
$logger->request->info("[1] This should be logged to configured log targets");

print_r($logger);

$logger->auth->notice("[1] User logged in");
$logger->system->info("[1] This message is logged in the system log.");

// 
// Setup multiple targets. The config service is not used as options 
// are passed direct:
// 
$options = [
        'request' => [
                'type'    => 'request',
                'options' => [
                        'path'  => '/tmp/batchelor',
                        'ident' => 'batchelor'
                ]
        ],
        'auth'    => [
                'type'    => 'syslog',
                'options' => [
                        'ident'    => 'batchelor',
                        'facility' => LOG_AUTH
                ]
        ]
];
$logger = new Logger($options);
$logger->request->info("[2] This should be logged to configured log targets");

print_r($logger);

$logger->auth->notice("[2] User logged in");

// 
// Setup multiplexer for auth target:
// 
$options = [
        'auth' => [
                'type'    => 'multiplex',
                'options' => [
                        // 
                        // The array key is used as logger type. Each logger can
                        // have a span of priorities.
                        // 
                        'request' => [
                                'options' => [
                                        'path'  => '/tmp/batchelor',
                                        'ident' => 'auth'
                                ]
                        ],
                        // 
                        // Combined auth log were each entry has ident set to
                        // remote address.
                        // 
                        'file'    => [
                                'options'  => [
                                        'filename' => '/tmp/batchelor/auth.log',
                                        'ident'    => filter_input(INPUT_SERVER, 'REMOTE_ADDR')
                                ],
                                'priority' => [
                                        'start' => LOG_INFO,
                                        'end'   => LOG_EMERG
                                ]
                        ],
                        // 
                        // Only priority warning or higher ends up in syslog.
                        // 
                        'syslog'  => [
                                'options'  => [
                                        'ident'    => 'batchelor',
                                        'facility' => LOG_AUTH
                                ],
                                'priority' => [
                                        'start' => LOG_WARNING
                                ]
                        ]
                ]
        ]
];
$logger = new Logger($options);
$logger->auth->info("[3] This should only be logged to reguest logger");
$logger->auth->warning("[3] This should be logged to all loggers");
$logger->auth->debug("[3] This should only appear in the request log");

print_r($logger);
