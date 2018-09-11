<?php

use Batchelor\Cache\Backend\Extension\Detected;

// 
// Use common test and data for file cache example/test.
// 
// $cache = new Detected([
//      'exclude' => ['xcache', 'apcu']
// ]);
// 

$cache = new Detected();
include('support/test.inc');

printf("\nCache backend: %s\n", print_r($cache, true));
