<?php

use Batchelor\Cache\Backend;

function test_support(Backend $cache)
{
        printf("KEY: %s\n", $cache->getCacheKey('data1'));
        printf("OPTIONS: %s\n", print_r($cache->getOptions(), true));
}

function test_cache_exist_name(Backend $cache, array $data)
{
        foreach (array_keys($data) as $key) {
                $result = $cache->exists($key);
                printf("EXIST (S): %b\n", $result);
        }
}

function test_cache_exist_keys(Backend $cache, array $data)
{
        $result = $cache->exists(array_keys($data));
        printf("EXIST (M): %s\n", print_r($result, true));
}

function test_cache_read_name(Backend $cache, array $data)
{
        foreach (array_keys($data) as $key) {
                $result = $cache->read($key);
                printf("READ (S): %s\n", print_r($result, true));
        }
}

function test_cache_read_keys(Backend $cache, array $data)
{
        $result = $cache->read(array_keys($data));
        printf("READ (M): %s\n", print_r($result, true));
}

function test_cache_save_name(Backend $cache, array $data)
{
        foreach ($data as $key => $val) {
                $result = $cache->save($key, $val);
                printf("SAVE (S): %s\n", $result);
        }
}

function test_cache_save_keys(Backend $cache, array $data)
{
        $result = $cache->save($data);
        printf("SAVE (M): %s\n", print_r($result, true));
}

function test_cache_delete_name(Backend $cache, array $data)
{
        foreach (array_keys($data) as $key) {
                $result = $cache->delete($key);
                printf("DELETE (S): %b\n", $result);
        }
}

function test_cache_delete_keys(Backend $cache, array $data)
{
        $result = $cache->delete(array_keys($data));
        printf("DELETE (M): %s\n", print_r($result, true));
}

function test_cache_exist(Backend $cache, array $data)
{
        test_cache_exist_name($cache, $data);
        test_cache_exist_keys($cache, $data);
}

function test_cache_read(Backend $cache, array $data)
{
        test_cache_read_name($cache, $data);
        test_cache_read_keys($cache, $data);
}

function test_cache_save(Backend $cache, array $data)
{
        test_cache_save_name($cache, $data);
        test_cache_save_keys($cache, $data);
}

function test_cache_delete(Backend $cache, array $data)
{
        test_cache_delete_name($cache, $data);
        test_cache_delete_keys($cache, $data);
}

function run_test(Backend $cache, array $data)
{
        test_support($cache);

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
}

$data = require('data.inc');
run_test($cache, $data);
