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
use Memcached as MemcachedSystem;

/**
 * The memcached backend.
 * 
 * Standard is that the backend creates the server connection object, but it's
 * possible to use an already instantiated object:
 * 
 * <code>
 * // 
 * // Create the server(s) connection object:
 * // 
 * $instance  = new \Memcached();
 * $instance->addServer('localhost', 11211);
 * 
 * // 
 * // Pass instance to cache backend:
 * // 
 * $backend = new Memcached([
 *      'instance' => $instance
 * ]);
 * </code>
 * 
 * For letting the backend class instantiate the server connection object, use 
 * either the server or servers options. The persistent options can also be used
 * in this case:
 * 
 * <code>
 * // 
 * // Single server:
 * // 
 * $backend = new Memcached([
 *      'server' => [ 
 *              'host' => 'localhost', 
 *              'port' => 11211 
 *      ]
 * ]);
 * 
 * // 
 * // Single server:
 * // 
 * $backend = new Memcached([
 *      'persistent' => 'my-pool',
 *      'server' => [ 
 *              'host' => 'localhost', 
 *              'port' => 11211 
 *      ]
 * ]);
 * 
 * // 
 * // Multiple servers (some weighted):
 * // 
 * $backend = new Memcached([
 *      'servers' => [ 
 *              [ 'server1.example.com', 11211     ],
 *              [ 'server2.example.com', 11211, 66 ],
 *              [ 'server3.example.com', 11211, 33 ]
 *      ]
 * ]);
 * </code>
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Memcached extends Base implements Backend
{

        /**
         * The memcached instance.
         * @var MemcachedSystem 
         */
        private $_instance;

        /**
         * Constructor.
         * @param array $options The cache options.
         */
        public function __construct($options = [])
        {
                if (!extension_loaded("memcached")) {
                        throw new BadFunctionCallException("The memcached extension is not loaded");
                }

                parent::__construct($options, [
                        'format'   => 'native',
                        'prefix'   => 'batchelor',
                        'lifetime' => 28800
                ]);

                $this->setInstance();
                $this->addServers();
        }

        /**
         * Set memcached connection object.
         */
        private function setInstance()
        {
                if (($instance = $this->getOption('instance'))) {
                        $this->_instance = $instance;
                } elseif (($persistent = $this->getOption('persistent'))) {
                        $this->_instance = new MemcachedSystem($persistent);
                } else {
                        $this->_instance = new MemcachedSystem();
                }
        }

        /**
         * Get memcached connection object.
         * @return MemcachedSystem
         */
        public function getInstance()
        {
                return $this->_instance;
        }

        /**
         * Add servers to connetion object.
         */
        private function addServers()
        {
                if (($server = $this->getOption('server'))) {
                        $this->_instance->addServers([$server]);
                }
                if (($servers = $this->getOption('servers'))) {
                        $this->_instance->addServers($servers);
                }

                if (($servers = $this->_instance->getServerList()) !== null) {
                        if (count($servers) == 0) {
                                $this->_instance->addServer('localhost', 11211);
                        }
                }
        }

        /**
         * {@inheritdoc}
         */
        public function delete($key)
        {
                $instance = $this->_instance;

                $command = new Delete($this, $key);
                $command->applyAll(function($keys) use($instance) {
                        $result = $instance->deleteMulti(array_keys($keys));
                        return $result;
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
                                if (!($res = $instance->get($key))) {
                                        $result[$key] = false;
                                } elseif ($instance->getResultCode() == MemcachedSystem::RES_NOTFOUND) {
                                        $result[$key] = false;
                                } elseif ($instance->getResultCode() == MemcachedSystem::RES_SUCCESS) {
                                        $result[$key] = true;
                                } else {
                                        $result[$key] = false;
                                }
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
                        $result = $instance->getMulti(array_keys($keys));
                        foreach ($result as $key => $val) {
                                $result[$key] = $formatter->onRead($val);
                        }
                        return $result;
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
                        if ($lifetime != 0) {
                                $expires = time() + $lifetime;
                        } else {
                                $expires = 0;
                        }

                        if (($result = $instance->setMulti($keys, $expires))) {
                                return array_fill_keys(array_keys($keys), true);
                        } else {
                                return array_fill_keys(array_keys($keys), false);
                        }
                });
                return $command->getResult();
        }

}
