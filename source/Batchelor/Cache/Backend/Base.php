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

use Batchelor\Cache\Backend;
use Batchelor\Cache\Factory;
use Batchelor\Cache\Formatter;
use Batchelor\System\Component;

/**
 * Description of Base
 *
 * @author Anders Lövgren (Nowise Systems)
 */
abstract class Base extends Component implements Backend
{

        /**
         * The read/save formatter.
         * @var Formatter 
         */
        private $_formatter;
        /**
         * Options for file cache.
         * @var array 
         */
        private $_options;

        /**
         * Constructor.
         * @param array $options The backend options.
         * @param array $default The default options.
         */
        public function __construct($options, $default)
        {
                parent::__construct();

                $options = array_merge($default, $options);

                if (!isset($options['format'])) {
                        $options['format'] = 'native';
                }
                if (!isset($options['prefix'])) {
                        $options['prefix'] = 'batchelor';
                }

                $this->useFormatter($options['format']);
                $this->setOptions($options);
        }

        /**
         * Use formatter type.
         * @param string $format The format type.
         */
        public function useFormatter(string $format)
        {
                $formatter = Factory::getFormatter($format);
                $this->_formatter = $formatter;
        }

        /**
         * {@inheritdoc}
         */
        public function getFormatter(): Formatter
        {
                return $this->_formatter;
        }

        /**
         * {@inheritdoc}
         */
        public function setFormatter(Formatter $formatter)
        {
                $this->_formatter = $formatter;
        }

        /**
         * {@inheritdoc}
         */
        public function setLifetime(int $lifetime)
        {
                $this->_options['lifetime'] = $lifetime;
        }

        /**
         * {@inheritdoc}
         */
        public function setPrefix(string $prefix)
        {
                $this->_options['prefix'] = $prefix;
        }

        /**
         * Get cache key.
         * 
         * The key argument is a string without prefix. This method applies
         * the configured prefix and return the result.
         * @param string $key The bare key name.
         */
        public function getCacheKey(string $key): string
        {
                return sprintf("%s-%s", $this->_options['prefix'], $key);
        }

        /**
         * Get key lifetime.
         * 
         * This returns the configured key lifetime if the lifetime argument
         * is not set, is false or null.
         * 
         * @param int $lifetime The key lifetime.
         * @return int
         */
        protected function getLifetime(int $lifetime = 0)
        {
                if (!isset($this->_options['lifetime'])) {
                        return $lifetime;
                } elseif (!isset($lifetime)) {
                        return 0;
                } elseif ($lifetime === 0 || $lifetime === false) {
                        return $this->_options['lifetime'];
                } else {
                        return $lifetime;
                }
        }

        /**
         * {@inheritdoc}
         */
        public function getOptions(): array
        {
                return $this->_options;
        }

        /**
         * {@inheritdoc}
         */
        public function getOption(string $name, $default = false)
        {
                return $this->_options[$name] ?? $default;
        }

        /**
         * Set options array.
         * @param array $options The cache options.
         */
        private function setOptions(array $options)
        {
                $this->_options = $options;
        }

}
