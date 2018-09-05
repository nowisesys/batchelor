<?php

use Batchelor\Cache\Backend\Memcached;

// 
// Use common test and data for file cache example/test.
// 

$cache = new Memcached();
include("support/test.inc");

printf("\nServer connection(s): %s\n", print_r(
        $cache->getInstance()->getServerList(), true
));
