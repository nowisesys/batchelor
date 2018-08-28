<?php

// 
// Notice that this is just testing the service class itself. The backends that can
// be used with it are tested more thoroughly in separate examples.
// 

use Batchelor\System\Persistance\Storage\SessionStorage;
use Batchelor\System\Service\Persistance;

// 
// Test persistance service:
// 
$service = new Persistance();

// 
// Store a simple value:
// 
$name = 'test1';
$data = 'data1';

$service->save($name, $data);

// 
// Store an array:
// 
$name = 'test2';
$data = ['k1' => 'v1', 'k2' => ['k3' => 'v3']];

$service->store($name, $data);
print_r($service->fetch($name));

// 
// Store an object:
// 
$name = 'test3';
$data = new RecursiveArrayObject(['k1' => 'v1', 'k2' => ['k3' => 'v3']]);

$service->store($name, $data);
print_r($service->fetch($name));

// 
// Change persistance backend:
// 
$service->setBackend(new SessionStorage());

// 
// Stored in session:
// 
$name = 'test4';
$data = 'data4';

$service->save($name, $data);
