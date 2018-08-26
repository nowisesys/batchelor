<?php

use Batchelor\System\Persistance\Cookie;

// 
// Test cookie class:
// 
$name = 'test1';
$data = 'data1';

$cookie = new Cookie($name, $data);
$cookie->save();

$name = 'test2';
$data = 'data2';

$cookie = new Cookie($name, $data, time() + 3600);
$cookie->save();

$name = 'test3';
$data = 'data3';

$cookie = new Cookie($name, $data, time() + 5400);
$cookie->save();
