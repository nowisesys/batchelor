<?php

use Batchelor\Cache\Backend\Redis;

// 
// Use common test and data for file cache example/test.
// 

$cache = new Redis();
include("support/test.inc");
