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

namespace Batchelor\Logging;

use Batchelor\System\Component;
use Batchelor\System\Service\Config;

/**
 * The logger service.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Service extends Component
{

        /**
         * The registered loggers.
         * @var array 
         */
        private $_loggers = [];

        /**
         * Constructor.
         * @param array $options The optional settings.
         */
        public function __construct(array $options = [])
        {
                if (empty($options)) {
                        $options = $this->getConfig();
                }
                if (isset($options)) {
                        $this->setLoggers($options);
                }
        }

        /**
         * Add logger.
         * 
         * @param string $name The logger identifier (i.e. request).
         * @param Logger $logger The target logger.
         */
        public function addLogger(string $name, Logger $logger)
        {
                $this->_loggers[$name] = $logger;
        }

        /**
         * Check if logger exists.
         * @param string $name The logger identifier (i.e. request).
         * @return bool
         */
        public function hasLogger(string $name): bool
        {
                return isset($this->_loggers[$name]);
        }

        /**
         * Get target logger.
         * @param string $name The logger identifier (i.e. request).
         * @return Logger
         */
        public function getLogger(string $name): Logger
        {
                return $this->_loggers[$name];
        }

        /**
         * Remove logger.
         * @param string $name The logger identifier (i.e. request).
         */
        public function removeLogger(string $name)
        {
                unset($this->_loggers[$name]);
        }

        /**
         * Set loggers.
         * @param array $options The logger settings.
         */
        private function setLoggers(array $options)
        {
                foreach ($options as $name => $data) {
                        $this->addLogger(
                            $name, Factory::getObject($data)
                        );
                }
        }

        /**
         * Get service configuration.
         * @return array 
         */
        private function getConfig(): array
        {
                if (($config = $this->getService("app"))) {
                        if (!isset($config->logger)) {
                                return self::getDefaults();
                        } else {
                                return array_replace_recursive(
                                    self::getDefaults(), Config::toArray($config->logger)
                                );
                        }
                }
        }

        /**
         * Get default logger settings.
         * @return array
         */
        private static function getDefaults(): array
        {
                return [
                        'request' => [
                                'type'    => 'request',
                                'options' => [
                                        'path'  => 'logs',
                                        'ident' => 'request'
                                ]
                        ],
                        'system'  => [
                                'type'    => 'file',
                                'options' => [
                                        'filename' => 'logs/system.log'
                                ]
                        ],
                        'auth'    => [
                                'type'    => 'syslog',
                                'options' => [
                                        'ident'    => 'batchelor',
                                        'facility' => LOG_AUTH
                                ]
                        ]
                ];
        }

}
