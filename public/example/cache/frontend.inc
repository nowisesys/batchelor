<?php

use Batchelor\Cache\Frontend;

// 
// Use common test and data for file cache example/test.
// 

$cache = new Frontend('file');
include('support/test.inc');
