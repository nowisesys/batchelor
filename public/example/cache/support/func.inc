<?php

// 
// Test cache functionality. 
// 


use Batchelor\Cache\Backend;
use Batchelor\Cache\Frontend;
use Batchelor\Cache\Storage;
use Batchelor\System\Service\Cache;

function test_interface(Storage $cache)
{
        if ($cache instanceof Backend) {
                test_class($cache);
                test_support($cache);
        }
        if ($cache instanceof Frontend) {
                test_class($cache);
                test_class($cache->getBackend());
                test_support($cache->getBackend());
        }
        if ($cache instanceof Cache) {
                test_class($cache);
                test_class($cache->getFrontend());
                test_class($cache->getFrontend()->getBackend());
                test_support($cache->getFrontend()->getBackend());
        }
}

function test_class(Storage $cache)
{
        printf("CLASS: %s\n", get_class($cache));
}

function test_support(Backend $cache)
{
        printf("KEY: %s\n", $cache->getCacheKey('data1'));
        printf("OPTIONS: %s\n", print_r($cache->getOptions(), true));
}

function test_cache_exist_name(Storage $cache, array $data)
{
        foreach (array_keys($data) as $key) {
                $result = $cache->exists($key);
                printf("EXIST (S): %b\n", $result);
        }
}

function test_cache_exist_keys(Storage $cache, array $data)
{
        $result = $cache->exists(array_keys($data));
        printf("EXIST (M): %s\n", print_r($result, true));
}

function test_cache_read_name(Storage $cache, array $data)
{
        foreach (array_keys($data) as $key) {
                $result = $cache->read($key);
                printf("READ (S): %s\n", print_r($result, true));
        }
}

function test_cache_read_keys(Storage $cache, array $data)
{
        $result = $cache->read(array_keys($data));
        printf("READ (M): %s\n", print_r($result, true));
}

function test_cache_save_name(Storage $cache, array $data)
{
        foreach ($data as $key => $val) {
                $result = $cache->save($key, $val);
                printf("SAVE (S): %s\n", $result);
        }
}

function test_cache_save_keys(Storage $cache, array $data)
{
        $result = $cache->save($data);
        printf("SAVE (M): %s\n", print_r($result, true));
}

function test_cache_delete_name(Storage $cache, array $data)
{
        foreach (array_keys($data) as $key) {
                $result = $cache->delete($key);
                printf("DELETE (S): %b\n", $result);
        }
}

function test_cache_delete_keys(Storage $cache, array $data)
{
        $result = $cache->delete(array_keys($data));
        printf("DELETE (M): %s\n", print_r($result, true));
}

function test_cache_exist(Storage $cache, array $data)
{
        test_cache_exist_name($cache, $data);
        test_cache_exist_keys($cache, $data);
}

function test_cache_read(Storage $cache, array $data)
{
        test_cache_read_name($cache, $data);
        test_cache_read_keys($cache, $data);
}

function test_cache_save(Storage $cache, array $data)
{
        test_cache_save_name($cache, $data);
        test_cache_save_keys($cache, $data);
}

function test_cache_delete(Storage $cache, array $data)
{
        test_cache_delete_name($cache, $data);
        test_cache_delete_keys($cache, $data);
}

function run_test(Storage $cache, array $data)
{
        $stime = microtime(true);

        test_interface($cache);

        printf("(i) Cleanup in cache:\n");
        test_cache_delete($cache, $data);

        printf("(i) Keys should be missing:\n");
        test_cache_exist($cache, $data);
        test_cache_read($cache, $data);

        printf("(i) Insert cache data:\n");
        test_cache_save($cache, $data);

        printf("(i) Keys should exist now:\n");
        test_cache_exist($cache, $data);
        test_cache_read($cache, $data);

        printf("(i) Cleanup in cache:\n");
        test_cache_delete($cache, $data);

        $etime = microtime(true);
        printf("(i) Test completed in %.06f sec\n", $etime - $stime);
}

$data = require('data.inc');
run_test($cache, $data);
