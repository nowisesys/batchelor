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

namespace Batchelor\System;

use RuntimeException;

/**
 * The services injector.
 * 
 * Supports lazy initalization of system services. Register the wrapper or callback 
 * with the services object to get it instantiated on demand.
 * 
 * <code>
 * // 
 * // Use shared instance:
 * // 
 * $services = Services::getInstance();
 * 
 * // 
 * // Register service using wrapper:
 * // 
 * $services->register('hostid', new Service(Hostid::class));
 * $services->register('hostid', new Service(Hostid::class, array('value' => 'standard-queue')));
 * 
 * // 
 * // Register service using callback:
 * // 
 * $services->register('hostid', function() { return new Hostid(); });
 * </code>
 * 
 * The services object will by default initialize itself by reading the 
 * file config/services.inc. It's also possible to pass an array of services
 * or another config file.
 * 
 * <code>
 * // 
 * // These two examples yields the same result:
 * // 
 * $conffile = "my-services.conf";
 * $services = new Services($conffile);                 // Implicit read config file.
 * $services = new Services(require($conffile));        // Explicit read config file.
 * 
 * // 
 * // Registered services can either be added or replaced. This is is primarly
 * // meant to support merging service registries.
 * // 
 * $services->addServices(require($conffile));
 * $services->setServices(require($conffile));
 * </code>
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Services
{

        /**
         * The registered service.
         * @var array 
         */
        private $_services = [];
        /**
         * The shared services object.
         * @var Services 
         */
        private static $_instance;

        /**
         * Constructor.
         * 
         * @param string|array $config The services config.
         * @throws RuntimeException
         */
        public function __construct($config = null)
        {
                if (!isset($config)) {
                        $config = $this->getConfigPath();
                }

                if (is_string($config) && !file_exists($config)) {
                        throw new RuntimeException("The services config file is missing ($config)");
                }

                if (is_array($config)) {
                        $this->setServices($config);
                } elseif (is_string($config)) {
                        $this->setServices(require($config));
                } else {
                        throw new InvalidArgumentException("Expected null, array or string as argument");
                }
        }

        /**
         * Get shared services instance.
         * @return Services
         */
        public static function getInstance()
        {
                if (!isset(self::$_instance)) {
                        return self::$_instance = new Services();
                } else {
                        return self::$_instance;
                }
        }

        /**
         * Set shared services instance.
         * @param Services $services The services object.
         */
        public static function setInstance($services)
        {
                self::$_instance = $services;
        }

        /**
         * Register an service object.
         * 
         * An already registered service by that name will be replaced. This is to
         * support upgrade of immutable services. 
         * 
         * @param string $name The service name.
         * @param Service|callable|object $object The service class.
         */
        public function register($name, $object)
        {
                $this->_services[$name] = $object;
        }

        /**
         * Check if service exist.
         * 
         * @param string $name The service name.
         * @return bool
         */
        public function hasService($name)
        {
                return isset($this->_services[$name]);
        }

        /**
         * Get registered service.
         * 
         * @param string $name The service name.
         * @return object
         */
        public function getService($name)
        {
                if (!$this->hasService($name)) {
                        throw new RuntimeException("The requested service $name is not registered");
                }
                if ($this->hasWrapper($name)) {
                        $this->useWrapper($name);
                }
                if ($this->hasCallable($name)) {
                        $this->useCallable($name);
                }

                return $this->_services[$name];
        }

        /**
         * Get all registered services.
         * @return array
         */
        public function getServices()
        {
                return $this->_services;
        }

        /**
         * Set all registered services.
         * @param array $config The list of services.
         */
        public function setServices($config)
        {
                $this->_services = $config;
        }

        /**
         * Add registered services.
         * @param array $config The list of services.
         */
        public function addServices($config)
        {
                $this->_services = array_merge($config, $this->_services);
        }

        /**
         * Check if $name refers to an service wrapper.
         * 
         * @param string $name The service name.
         * @return bool
         */
        private function hasWrapper($name)
        {
                return $this->_services[$name] instanceof Service;
        }

        /**
         * Create object from service wrapper.
         * @param string $name The service name.
         */
        private function useWrapper($name)
        {
                $wrapper = $this->_services[$name];
                $this->_services[$name] = $wrapper->getObject();
        }

        /**
         * Check if name referes to an callable.
         * 
         * @param string $name The service name.
         * @return bool
         */
        private function hasCallable($name)
        {
                return is_callable($this->_services[$name]);
        }

        /**
         * Create object from callable.
         * @param string $name The service name.
         */
        private function useCallable($name)
        {
                $callback = $this->_services[$name];
                $this->_services[$name] = call_user_func($callback, $name);
        }

        /**
         * Get config file path.
         * @return string
         */
        private function getConfigPath(): string
        {
                if (defined('APP_ROOT')) {
                        return sprintf("%s/config/services.inc", APP_ROOT);
                } elseif (getenv('APP_ROOT')) {
                        return sprintf("%s/config/services.inc", getenv('APP_ROOT'));
                } else {
                        return realpath(__DIR__ . "/../../../config/services.inc");
                }
        }

}
