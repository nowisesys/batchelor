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

namespace Batchelor\Cache;

use Batchelor\System\Service\Config as ApplicationConfig;
use Batchelor\System\Services;
use RuntimeException;

/**
 * Cache config loader.
 * 
 * Detect cache options for a sub system (i.e. the queue mapper) from application
 * config and merge with supplied options. Sensitive defaults are applied for 
 * missing values.
 * 
 * <code>
 * $config = new Config('mapper', 'persist');
 * $options = $config->getOptions();
 * </code>
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Config
{

        /**
         * The options to use.
         * @var array 
         */
        private $_options;

        /**
         * Constructor.
         * 
         * @param string $name The config name.
         * @param string $type The default cache type (default to detect).
         * @param array $options The cache options.
         */
        public function __construct(string $name, string $type = 'detect', array $options = [])
        {
                $config = $this->getConfig($name, $type);

                if (empty($config['options'])) {
                        $config['options'] = $options;
                } else {
                        $config['options'] = array_merge($config['options'], $options);
                }

                if ($config['type'] == 'file') {
                        $config['options']['path'] = "cache/$name";
                        $config['options']['prefix'] = "";
                }

                if (!isset($config['options']['prefix'])) {
                        $config['options']['prefix'] = "batchelor-$name";
                }

                $this->_options = $config;
        }

        /**
         * Get cache config options.
         * @return array
         */
        public function getOptions(): array
        {
                return $this->_options;
        }

        /**
         * Get cache config.
         * 
         * @param string $name The config name.
         * @param string $type The default cache type.
         * @return array
         * @throws RuntimeException
         */
        private function getConfig(string $name, string $type): array
        {
                $config = $this->getService();

                if (!$config->cache) {
                        return ['type' => $type];
                }

                if (!$config->cache->offsetExists($name) &&
                    !$config->cache->offsetExists("@all")) {
                        return ['type' => $type];
                } else {
                        $name = $this->getSection($config, $name);
                }

                if (!($entry = $config->cache->offsetGet($name))) {
                        throw new RuntimeException("Failed read cache config for $name");
                } elseif (is_string($entry)) {
                        return ['type' => $entry];
                } elseif (is_bool($entry) && $entry == true) {
                        return ['type' => $entry];
                } else {
                        return Config::toArray($entry);
                }
        }

        /**
         * Get config section to use.
         * 
         * @param ApplicationConfig $config The application config.
         * @param string $name The cache sub system name.
         * @return string
         */
        private function getSection(ApplicationConfig $config, string $name): string
        {
                if ($config->cache->offsetExists($name)) {
                        return $name;
                }
                if ($config->cache->offsetExists("@all")) {
                        return "@all";
                }
        }

        /**
         * Get config service.
         * @return Config
         */
        private function getService(): ApplicationConfig
        {
                return Services::getInstance()->getService("app");
        }

}
