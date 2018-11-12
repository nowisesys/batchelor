<?php

use Batchelor\Cache\Backend\Extension\ShmOp;

// 
// Use common test and data for file cache example/test.
// 

$cache = new ShmOp();
include("support/test.inc");

?>
