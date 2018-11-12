<?php

use Batchelor\Cache\Backend\Extension\Passthru;

// 
// Use common test and data for file cache example/test.
// 

$cache = new Passthru();
include('support/test.inc');
