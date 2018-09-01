<?php

use Batchelor\System\Service\Config;

// 
// Test reading system config (defaults.app)
// 

$config = new Config();

printf("Filename: %s\n", $config->getFilename());
printf("Data directory: %s\n", $config->data->directory);

// 
// Config is read-only by default:
// 
$config->key1 = "value1";
printf("Test 1: %s\n", $config->key1);

// 
// Test set writable:
// 
printf("Read-only (a): %b\n", $config->isReadOnly());
$config->setMutable();
printf("Read-only (b): %b\n", $config->isReadOnly());

$config->key2 = "value2";
printf("Test 2: %s\n", $config->key2);

// 
// The read-only property affects only top level entries. For others that 
// setting is only advisory. 
// 
$config->setMutable(false);
printf("Read-only (c): %b\n", $config->isReadOnly());

// 
// Modify the cache entry will be fine even though the config is flagged
// as read-only:
// 
$config->data->backend = [
        'type' => 'redis',
        'host' => 'localhost',
        'port' => 6379
];

// 
// Dump internal data:
// 
print_r($config->getConfig());
