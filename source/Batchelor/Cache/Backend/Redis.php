<?php

/*
 * Copyright (C) 2018 Anders Lövgren (Nowise Systems)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace Batchelor\Cache\Backend;

use BadFunctionCallException;
use Batchelor\Cache\Backend;
use Batchelor\Cache\Command\Delete;
use Batchelor\Cache\Command\Exists;
use Batchelor\Cache\Command\Read;
use Batchelor\Cache\Command\Save;
use LogicException;
use Redis as RedisServer;
use RedisArray;
use RedisCluster;

/**
 * The redis backend.
 * 
 * Standard is that the backend creates the server connection object, but it's
 * possible to use an already instantiated object:
 * 
 * <code>
 * // 
 * // Create the server(s) connection object:
 * // 
 * $instance  = new \Redis();
 * $instance->connect('localhost', 6379);
 * 
 * // 
 * // Pass instance to cache backend:
 * // 
 * $backend = new Redis([
 *      'instance' => $instance
 * ]);
 * </code>
 * 
 * When passing an instance you are not limited to a single server instance as
 * above. The instance can be connected to a single server, cluster or array of
 * servers.
 * 
 * The simplest case are a single server. A lot of default options are assumed 
 * for missing options:
 * 
 * <code>
 * // 
 * // Single server using localhost and default port:
 * // 
 * $backend = new Redis();
 * 
 * // 
 * // Connect to UNIX socket:
 * // 
 * $backend = new Redis([
 *      'server' => [
 *              'host' => '/tmp/redis.sock'
 *      ]
 * ]);
 * 
 * // 
 * // Single server:
 * // 
 * $backend = new Redis([
 *      'server' => [ 
 *              'host' => 'localhost', 
 *              'port' => 6379 
 *      ]
 * ]);
 * 
 * // 
 * // Single server with persistent connection, using all options:
 * // 
 * $backend = new Redis([
 *      'persistent' => 'my-pool',                      // Optional (string|bool)
 *      'server' => [ 
 *              'host'           => 'localhost',        // Optional (string)
 *              'port'           => 6379,               // Optional (int)
 *              'timeout'        => 2.5,                // Optional (float)
 *              'retry_interval' => 100,                // Optional (in millisec)
 *              'read_timeout'   => 5                   // Optional (in seconds)
 *      ]
 * ]);
 * 
 * // 
 * // Use password to authenticate the connection. Pass database to change the
 * // selected database:
 * // 
 * $backend = new Redis([
 *      'password' => 'secret',
 *      'database' => 6
 * ])
 * </code>
 * 
 * Cluster can be instantiated using either the cluster name (requires system 
 * setting in i.e. redis.ini) or from an servers array:
 * 
 * </code>
 * // 
 * // Use named cluster:
 * // 
 * $backend = new Redis([
 *      'cluster' => [
 *              'name' => 'mycluster'
 *      ]
 * ]);
 * 
 * // 
 * // Use array of hosts (using options are optional):
 * // 
 * $backend = new Redis([
 *      'cluster' => [
 *              'servers' => [
 *                      'host:7000', 'host:7001', 'host:7003'
 *              ],
 *              'options' => [
 *                      ...     // i.e. timeout
 *              ]
 *      ]
 * ]);
 * </code>
 * 
 * Array of hosts can be instantiated either by name (requires system settings in
 * i.e. redis.ini) or from an servers array:
 * 
 * <code>
 * // 
 * // Use named array:
 * // 
 * $backend = new Redis([
 *      'array' => [
 *              'name' => 'mycluster'
 *      ]
 * ]);
 * 
 * // 
 * // Use array of hosts (using options are optional):
 * // 
 * $backend = new Redis([
 *      'array' => [
 *              'servers' => [
 *                      'host:7000', 'host:7001', 'host:7003'
 *              ],
 *              'options' => [                  // See docs for possible options
 *                      'retry_timeout' => 100,
 *                      'lazy_connect'  => true
 *              ]
 *      ]
 * ]);
 * </code>
 * 
 * @author Anders Lövgren (Nowise Systems)
 * 
 * @see https://github.com/phpredis/phpredis
 * @see https://github.com/phpredis/phpredis/blob/develop/cluster.markdown
 * @see https://github.com/phpredis/phpredis/blob/develop/arrays.markdown
 */
class Redis extends Base implements Backend
{

        /**
         * The redis instance.
         * @var RedisServer 
         */
        private $_instance;

        /**
         * Constructor.
         * @param array $options The cache options.
         */
        public function __construct($options = [])
        {
                if (!extension_loaded("redis")) {
                        throw new BadFunctionCallException("The redis extension is not loaded");
                }

                parent::__construct($options, [
                        'format'   => 'php',
                        'prefix'   => 'batchelor',
                        'lifetime' => 28800
                ]);

                $this->setInstance();
        }

        /**
         * Destructor.
         */
        public function __destruct()
        {
                $this->_instance->close();
        }

        /**
         * Set redis connection object.
         */
        private function setInstance()
        {
                if (($instance = $this->getOption('instance'))) {
                        $this->_instance = $instance;
                } elseif (($options = $this->getOption('cluster'))) {
                        $this->_instance = $this->getRedisCluster($options);
                } elseif (($options = $this->getOption('array'))) {
                        $this->_instance = $this->getRedisArray($options);
                } elseif (($options = $this->getOption('server'))) {
                        $this->_instance = $this->getRedisServer($options, $this->getOption('persistent'));
                } else {
                        $this->_instance = $this->getRedisServer([], $this->getOption('persistent'));
                }
        }

        /**
         * Get Redis array.
         * 
         * @param array $options The connection options.
         * @return RedisArray
         * @throws LogicException
         */
        private function getRedisArray(array $options)
        {
                if (isset($options['name'])) {
                        return new RedisArray($options['name']);
                } elseif (isset($options['options'])) {
                        return new RedisArray($options['servers'], $options['options']);
                } elseif (isset($options['servers'])) {
                        return new RedisArray($options['servers']);
                } else {
                        throw new LogicException("Missing name, servers or options in array mode");
                }
        }

        /**
         * Get Redis cluster.
         * 
         * @param array $options The connection options.
         * @return RedisCluster
         * @throws LogicException
         */
        private function getRedisCluster(array $options)
        {
                if (isset($options['name'])) {
                        return new RedisCluster($options['name']);
                } elseif (isset($options['options'])) {
                        return new RedisCluster(null, $options['servers'], ...array_values($options['options']));
                } elseif (isset($options['servers'])) {
                        return new RedisCluster(null, $options['servers']);
                } else {
                        throw new LogicException("Missing name, servers or options in cluster mode");
                }
        }

        /**
         * Get Redis instance.
         * 
         * @param array $options The connection options.
         * @param bool|string $persistent The persistent setting.
         * @return RedisServer
         */
        private function getRedisServer(array $options, $persistent)
        {
                $options = $this->getRedisParams($options, $persistent);

                if ($persistent) {
                        $redis = new RedisServer();
                        $redis->pconnect(...array_values($options));
                } else {
                        $redis = new RedisServer();
                        $redis->connect(...array_values($options));
                }

                if (($password = $this->getOption('password'))) {
                        $redis->auth($password);
                }
                if (($database = $this->getOption('database'))) {
                        $redis->select($database);
                }

                return $redis;
        }

        /**
         * Get Redis options.
         * 
         * @param array $options The options passed.
         * @param bool|string $persistent The persistent setting.
         * @return array
         */
        private function getRedisParams(array $options, $persistent): array
        {
                $default = [
                        'host'          => false,
                        'port'          => false,
                        'timeout'       => false,
                        'persistent_id' => false
                ];

                if (!isset($options['host'])) {
                        $options['host'] = 'localhost';
                }
                if (!isset($options['port'])) {
                        $options['port'] = 6379;
                }
                if (!isset($options['timeout'])) {
                        $options['timeout'] = 0;
                }
                if (is_string($persistent)) {
                        $options['persistent_id'] = $persistent;
                }

                return array_filter(array_merge($default, $options));
        }

        /**
         * Get redis connection object.
         * @return RedisServer
         */
        public function getInstance()
        {
                return $this->_instance;
        }

        /**
         * {@inheritdoc}
         */
        public function delete($key)
        {
                $instance = $this->_instance;

                $command = new Delete($this, $key);
                $command->applyAll(function($keys) use($instance) {
                        $instance->delete(array_keys($keys));
                        return array_fill(0, count($keys), true);
                });
                return $command->getResult(is_string($key));
        }

        /**
         * {@inheritdoc}
         */
        public function exists($key)
        {
                $instance = $this->_instance;

                $command = new Exists($this, $key);
                $command->applyAll(function($keys) use($instance) {
                        $result = [];

                        foreach (array_keys($keys) as $key) {
                                $result[$key] = $instance->exists($key) == 1;
                        }

                        return $result;
                });
                return $command->getResult(is_string($key));
        }

        /**
         * {@inheritdoc}
         */
        public function read($key)
        {
                $instance = $this->_instance;
                $formatter = $this->getFormatter();

                $command = new Read($this, $key);
                $command->applyAll(function($keys) use($instance, $formatter) {
                        $result = $instance->mget(array_keys($keys));
                        foreach (array_keys($keys) as $index => $key) {
                                $keys[$key] = $formatter->onRead($result[$index]);
                        }
                        return $keys;
                });
                return $command->getResult();
        }

        /**
         * {@inheritdoc}
         */
        public function save($key, $value = null, int $lifetime = 0)
        {
                $instance = $this->_instance;
                $formatter = $this->getFormatter();

                $command = new Save($this, $key, $value);
                $command->applyAll(function($keys) use($instance, $formatter, $lifetime) {
                        foreach ($keys as $key => $val) {
                                $keys[$key] = $formatter->onSave($val);
                        }

                        if (($success = $instance->mset($keys)) && $lifetime != 0) {
                                foreach (array_keys($keys) as $key) {
                                        $instance->expire($key, $lifetime);
                                }
                        }

                        if ($success) {
                                return array_fill_keys(array_keys($keys), true);
                        } else {
                                return array_fill_keys(array_keys($keys), false);
                        }
                });
                return $command->getResult();
        }

}
