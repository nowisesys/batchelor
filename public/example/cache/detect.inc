<?php

use Batchelor\Cache\Backend\Extension\Detected;

// 
// Use common test and data for file cache example/test.
// 

$cache = new Detected();
include('support/test.inc');

// 
// Specific backends can also be excluded:
// $cache = new Detected([
//      'exclude' => ['xcache', 'file']
// ]);
// 

printf("\nCache backend: %s\n", print_r($cache, true));
