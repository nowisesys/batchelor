<?php

use Batchelor\System\Persistance\Storage\CookieStorage;

// 
// Test cookie storage:
// 
$service = new CookieStorage();

// 
// Work with string:
// 
$name = 'test1';
$data = 'data1';

$service->save($name, $data);

$name = 'test2';
$data = 'data2';

$service->save($name, $data, time() + 3600);

// 
// Test utility functions:
// 
$name = 'test3';
$data = 'data3';

printf("EXIST ($name): %b\n", $service->exists($name));

$service->save($name, $data);

printf("EXIST ($name): %b\n", $service->exists($name));
printf("VALUE ($name): %s\n", $service->read($name));

// 
// Work with object:
// 
$name = 'test4';
$data = new RecursiveArrayObject([
        'k1' => 'v1',
        'k2' => [
                'k3' => 'v3',
                'k4' => 'v4',
                'k5' => [
                        'k6' => 'v6'
                ]
        ]
    ]);

printf("EXIST ($name): %b\n", $service->exists($name));

$service->store($name, $data, time() + 5400);

printf("EXIST ($name): %b\n", $service->exists($name));
printf("VALUE ($name): %s", print_r($service->fetch($name), true));
