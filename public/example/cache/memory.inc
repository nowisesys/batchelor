<?php

use Batchelor\Cache\Backend\Memory;

// 
// Use common test and data for file cache example/test.
// 

$cache = new Memory();
include("support/test.inc");
