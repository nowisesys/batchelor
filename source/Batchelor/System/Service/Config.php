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

namespace Batchelor\System\Service;

use Batchelor\Storage\Locate;
use RecursiveArrayObject;
use RuntimeException;

/**
 * The system config class.
 *
 * <code>
 * // 
 * // Create empty config:
 * // 
 * $config = new Config(null);
 * 
 * // 
 * // Load config file:
 * // 
 * $config = new Config("defaults.app");
 * $config->data->directory;   // i.e. /var/data/batchelor
 * 
 * // 
 * // Use absolute path:
 * // 
 * $config = new Config("/etc/myapp/system.conf");
 * 
 * // 
 * // Use custom search location:
 * // 
 * $config = new Config("system.conf", [ "/etc/myapp" ]);
 * </code>
 * 
 * @property-read RecursiveArrayObject $data Data directory settings.
 * @property-read RecursiveArrayObject $contact Contact address settings.
 * @property-read RecursiveArrayObject $cache Cache settings for application.
 * @property-read RecursiveArrayObject $logger Logger settings for application.
 * @property-read RecursiveArrayObject $rotate The rotate queue settings.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Config
{

        /**
         * The config data.
         * @var RecursiveArrayObject
         */
        private $_config;
        /**
         * The config filename.
         * @var string 
         */
        private $_filename;
        /**
         * Default is read-only.
         * @var bool 
         */
        private $_immutable = true;

        /**
         * Constructor.
         * @param string $config The config file.
         * @param array $locations Additional search locations.
         */
        public function __construct(string $config = "defaults.app", array $locations = [])
        {
                if (isset($config)) {
                        $this->useConfig($config, $locations);
                } else {
                        $this->setConfig(new RecursiveArrayObject());
                }
        }

        public function __get($name)
        {
                if ($this->_config->offsetExists($name)) {
                        return $this->_config->offsetGet($name);
                }
        }

        public function __set($name, $value)
        {
                if ($this->_immutable == false) {
                        $this->_config->offsetSet($name, $value);
                }
        }

        public function __unset($name)
        {
                if ($this->_immutable == false) {
                        $this->_config->offsetUnset($name);
                }
        }

        public function __isset($name)
        {
                return $this->_config->offsetExists($name);
        }

        /**
         * Check if config is read-only.
         * @return bool
         */
        public function isReadOnly()
        {
                return $this->_immutable;
        }

        /**
         * Change read-only mode.
         * 
         * The immutable property is only advisory and intended to prevent
         * changes at a basic level. 
         * 
         * Of course this will only affect the top level.
         * 
         * @param bool $enable Set read-only/writable mode.
         */
        public function setMutable(bool $enable = true)
        {
                $this->_immutable = ($enable == false);
        }

        /**
         * Check if config has data.
         * @return bool
         */
        public function hasConfig()
        {
                return $this->_config->count() != 0;
        }

        /**
         * Set config data.
         * @param RecursiveArrayObject $config The config data.
         */
        public function setConfig(RecursiveArrayObject $config)
        {
                $this->_config = $config;
        }

        /**
         * Get config object.
         * @return RecursiveArrayObject
         */
        public function getConfig()
        {
                return $this->_config;
        }

        /**
         * Get config filename.
         * @return string
         */
        public function getFilename()
        {
                return $this->_filename;
        }

        /**
         * Set config data.
         * 
         * @param string $config The config file.
         * @param array $locations Additional search locations.
         */
        private function useConfig(string $config, array $locations)
        {
                if (file_exists($config)) {
                        $this->loadConfig($config);
                } else {
                        $this->findConfig($config, $locations);
                }
        }

        /**
         * Load config from file.
         * @param string $config The config file.
         */
        private function loadConfig(string $config)
        {
                $this->_config = new RecursiveArrayObject(require($config));
                $this->_filename = realpath($config);
        }

        /**
         * Search for config file.
         * 
         * This method searches for the given file in a number of pre-defined
         * locations. If config file is found, then its used as the config data
         * file.
         * 
         * @param string $config The config file.
         * @param array $locations Additional search locations.
         */
        private function findConfig(string $config, array $locations)
        {
                $locate = new Locate($locations);
                $locate->addLocation("/etc/batchelor");

                if (!($target = $locate->getFilepath($config))) {
                        throw new RuntimeException("Failed locate config file $config");
                }

                $this->loadConfig($target);
        }

        /**
         * Get array recursive.
         * 
         * @param RecursiveArrayObject $config The config tree node.
         * @return array
         */
        public static function toArray(RecursiveArrayObject $config): array
        {
                foreach ($config as $key => $val) {
                        if (is_object($val)) {
                                $config[$key] = self::toArray($val);
                        }
                }

                return $config->getArrayCopy();
        }

}
