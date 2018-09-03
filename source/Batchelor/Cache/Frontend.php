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

use Batchelor\Cache\Formatter\NativeFormat;
use Batchelor\System\Component;

/**
 * The client side frontend.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Frontend extends Component implements Storage
{

        /**
         * The cache backend.
         * @var Backend 
         */
        private $_backend;
        /**
         * The read/save formatter.
         * @var Formatter 
         */
        private $_formatter;

        /**
         * Constructor.
         * @param array $options The cache config options.
         */
        public function __construct($options = null)
        {
                $this->_formatter = new NativeFormat();
                $this->_backend = Factory::getBackend($options);
        }

        /**
         * {@inheritdoc}
         */
        public function exists($key)
        {
                $backend = $this->_backend;
                return $backend->exists($key);
        }

        /**
         * {@inheritdoc}
         */
        public function read($key)
        {
                $backend = $this->_backend;
                $formatter = $this->_formatter;

                if (!($result = $backend->read($key))) {
                        return $result;
                } elseif (is_string($key)) {
                        return $formatter->onRead($result);
                }

                foreach ($result as $key => $val) {
                        $result[$key] = $formatter->onRead($val);
                }

                return $result;
        }

        /**
         * {@inheritdoc}
         */
        public function save($key, $value = null, int $lifetime = 0)
        {
                $backend = $this->_backend;
                $formatter = $this->_formatter;

                if (is_string($key)) {
                        return $backend->save($key, $formatter->onSave($value), $lifetime);
                }

                foreach ($key as $k => $v) {
                        $key[$k] = $formatter->onSave($v);
                }

                $this->save($key, $value, $lifetime);
        }

        /**
         * {@inheritdoc}
         */
        public function delete($key)
        {
                $backend = $this->_backend;
                $backend->delete($key);
        }

        /**
         * Get cache backend.
         * @return Backend The cache backend.
         */
        public function getBackend()
        {
                return $this->_backend;
        }

        /**
         * Set cache backend.
         * @param Backend $backend The cache backend.
         */
        public function setBackend(Backend $backend)
        {
                $this->_backend = $backend;
        }

        /**
         * Set read/save formatter.
         * @param Formatter $formatter The formatter object.
         */
        public function setFormatter(Formatter $formatter)
        {
                $this->_formatter = $formatter;
        }

}
