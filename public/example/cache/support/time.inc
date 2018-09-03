<?php

use Batchelor\Cache\Backend;

function test_cache_delete_time_key(Backend $cache, int $limit)
{
        $timer = microtime(true);

        for ($i = 0; $i < $limit; ++$i) {
                $cache->delete('data1');
        }

        $timer = microtime(true) - $timer;
        printf("TIMER (S): %.06f (DELETE:$limit)\n", $timer);
}

function test_cache_delete_time_arr(Backend $cache, array $array)
{
        $limit = count($array);
        $timer = microtime(true);

        $cache->delete($array);

        $timer = microtime(true) - $timer;
        printf("TIMER (M): %.06f (DELETE:$limit)\n", $timer);
}

function test_cache_delete_time(Backend $cache, array $array, int $limit)
{
        test_cache_delete_time_key($cache, $limit);
        test_cache_delete_time_arr($cache, $array);
}

function test_cache_exist_time_key(Backend $cache, int $limit)
{
        $timer = microtime(true);

        for ($i = 0; $i < $limit; ++$i) {
                $cache->exists('data1');
        }

        $timer = microtime(true) - $timer;
        printf("TIMER (S): %.06f (EXIST:$limit)\n", $timer);
}

function test_cache_exist_time_arr(Backend $cache, array $array)
{
        $limit = count($array);
        $timer = microtime(true);

        $cache->exists($array);

        $timer = microtime(true) - $timer;
        printf("TIMER (M): %.06f (EXIST:$limit)\n", $timer);
}

function test_cache_exist_time(Backend $cache, array $array, int $limit)
{
        test_cache_exist_time_key($cache, $limit);
        test_cache_exist_time_arr($cache, $array);
}

function test_cache_read_time_key(Backend $cache, int $limit)
{
        $timer = microtime(true);

        for ($i = 0; $i < $limit; ++$i) {
                $cache->read('data1');
        }

        $timer = microtime(true) - $timer;
        printf("TIMER (S): %.06f (READ:$limit)\n", $timer);
}

function test_cache_read_time_arr(Backend $cache, array $array)
{
        $limit = count($array);
        $timer = microtime(true);

        $cache->read($array);

        $timer = microtime(true) - $timer;
        printf("TIMER (M): %.06f (READ:$limit)\n", $timer);
}

function test_cache_read_time(Backend $cache, array $array, int $limit)
{
        test_cache_read_time_key($cache, $limit);
        test_cache_read_time_arr($cache, $array);
}

function test_cache_save_time_key(Backend $cache, int $limit)
{
        $timer = microtime(true);

        for ($i = 0; $i < $limit; ++$i) {
                $cache->save('data1', 'value1');
        }

        $timer = microtime(true) - $timer;
        printf("TIMER (S): %.06f (SAVE:$limit)\n", $timer);
}

function test_cache_save_time_arr(Backend $cache, array $array)
{
        $limit = count($array);
        $timer = microtime(true);

        $cache->save($array);

        $timer = microtime(true) - $timer;
        printf("TIMER (M): %.06f (SAVE:$limit)\n", $timer);
}

function test_cache_save_time(Backend $cache, array $array, int $limit)
{
        test_cache_save_time_key($cache, $limit);
        test_cache_save_time_arr($cache, $array);
}

function test_cache_array_prepare(int $limit)
{
        return array_fill_keys(
            array_map(function($i) {
                    return sprintf("data%06d", $i);
            }, range(1, $limit)
            ), 'value1');
}

function test_cache_array_delete(Backend $cache, array $array)
{
        $cache->delete(array_keys($array));
}

function run_time(Backend $cache, int $limit)
{
        $array = test_cache_array_prepare($limit);
        $akeys = array_keys($array);
        $stime = microtime(true);

        test_cache_exist_time($cache, $akeys, $limit);
        test_cache_save_time($cache, $array, $limit);
        test_cache_read_time($cache, $akeys, $limit);
        test_cache_delete_time($cache, $akeys, $limit);

        $etime = microtime(true);
        printf("(i) Test completed in %.06f sec\n", $etime - $stime);
        test_cache_array_delete($cache, $akeys);
}

$limit = $this->params->getParam('limit', 10000);
run_time($cache, $limit);
