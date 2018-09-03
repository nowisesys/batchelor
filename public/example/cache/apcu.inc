<?php

use Batchelor\Cache\Backend\APCu;

// 
// Use common test and data for file cache example/test.
// 

$cache = new APCu();
include("support/test.inc");
