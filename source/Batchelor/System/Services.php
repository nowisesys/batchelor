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

use ReflectionClass;
use RuntimeException;

/**
 * The services injector.
 * 
 * Register system services with this class to have them instantiated on demand
 * when requested. This class is intended to be used for service injection in
 * various places and to support user to drop in custom classes.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Services
{

        /**
         * The service config file.
         * @var string 
         */
        private $_config;
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
         * @param string $config The services config file.
         * @throws RuntimeException
         */
        public function __construct($config = null)
        {
                if (!isset($config)) {
                        $config = realpath(__DIR__ . "/../../../config/services.inc");
                }
                if (!file_exists($config)) {
                        throw new RuntimeException("The services config file is missing ($config)");
                }

                $this->setServices($config);
        }

        /**
         * Initialize the service registry.
         * @param string $config The services config file.
         */
        private function setServices($config)
        {
                $this->_services = require($config);
                $this->_config = $config;
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
         * Register an service class.
         * 
         * @param string $name The service name.
         * @param string $type The service class.
         * @param array $args Optional arguments for class constructor.
         */
        public function registerClass($name, $type, $args = null)
        {
                $this->_services[$name] = [
                        'name' => $type,
                        'args' => $args
                ];
        }

        /**
         * Register an service object.
         * 
         * @param string $name The service name.
         * @param object $object The service class.
         */
        public function registerObject($name, $object)
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
                if (!$this->hasObject($name)) {
                        list($type, $args) = $this->_services[$name];
                        return $this->setObject($name, $type, $args);
                } else {
                        return $this->getObject($name);
                }
        }

        /**
         * Check if service is instantiated.
         * 
         * @param string $name The service name.
         * @return bool
         */
        private function hasObject($name)
        {
                return isset($this->_services[$name]) && is_object($this->_services[$name]);
        }

        /**
         * Get registered object.
         * 
         * @param string $name The service name.
         * @return object
         */
        private function getObject($name)
        {
                return $this->_services[$name];
        }

        /**
         * Create and set object.
         * 
         * @param string $name The service name.
         * @param string $type The service class.
         * @param array $args Optional arguments for class constructor.
         * @return object
         */
        private function setObject($name, $type, $args)
        {
                $this->_services[$name] = (
                    new ReflectionClass($type)
                    )->newInstanceArgs($args);
                return $this->_services[$name];
        }

}
