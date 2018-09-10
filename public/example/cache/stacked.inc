<?php

use Batchelor\Cache\Backend\Extension\Stacked;

// 
// Use common test and data for file cache example/test.
// 

$cache = new Stacked();
$cache->setLifetime(10800);
$cache->addBackend("memory", new Batchelor\Cache\Backend\Memory());
$cache->addBackend("file", new Batchelor\Cache\Backend\File());
$cache->addBackend("apcu", new Batchelor\Cache\Backend\APCu());
$cache->addBackend("redis", new Batchelor\Cache\Backend\Redis());
include('support/test.inc');

printf("\nCache backends: %s\n", print_r(
        $cache->getBackends(), true
));
