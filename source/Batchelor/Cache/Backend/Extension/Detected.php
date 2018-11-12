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

namespace Batchelor\Cache\Backend\Extension;

use Batchelor\Cache\Backend;
use Batchelor\Cache\Backend\Base;
use Batchelor\Cache\Factory;
use Batchelor\Cache\Storage;

/**
 * Detect cache backend.
 * 
 * This is a pseudo-backend that detects available backends and uses the first 
 * detected as its proxied cache backend object.
 * 
 * <code>
 * $detected = new Detected([
 *      'lifetime' => 144400
 * ]);
 * $detected->save("key1", "val1");     // Save to APCu if was detected first.
 * </code>
 * 
 * The exclude list can also be passed inside the options array:
 * 
 * <code>
 * $detected = new Detected([
 *      'lifetime' => 144400,
 *      'exclude'  => [ 'xcache' ]      // Don't use xcache even if available
 * ]);
 * </code>
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Detected extends Base implements Storage
{

        /**
         * The cache backend.
         * @var Backend 
         */
        private $_backend;

        /**
         * Constructor.
         * @param array $options The backend options.
         * @param array $exclude The backends to exclude.
         */
        public function __construct(array $options = [], array $exclude = [])
        {
                parent::__construct($options, []);

                $exclude = $this->getOption("exclude", $exclude);
                $options = $this->getOptions();

                unset($options['format']);      // Use backend default
                unset($options['exclude']);     // Filter from backend

                $this->_backend = Factory::getFirst($options, $exclude);
        }

        /**
         * {@inheritdoc}
         */
        public function delete($key)
        {
                $backend = $this->_backend;
                return $backend->delete($key);
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
                return $backend->read($key);
        }

        /**
         * {@inheritdoc}
         */
        public function save($key, $value = null, int $lifetime = 0)
        {
                $backend = $this->_backend;
                return $backend->save($key, $value, $lifetime);
        }

        /**
         * Get inner backend.
         * @return Backend
         */
        public function getBackend(): Backend
        {
                return $this->_backend;
        }

}
