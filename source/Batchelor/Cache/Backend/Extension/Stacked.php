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
use Batchelor\Data\Structure\AssocArrayCompare;
use RuntimeException;

/**
 * The stacked backend.
 * 
 * Stacks one or more cache backends together inside an heap array. The backend 
 * with short lifetime gets higher priority. 
 * 
 * Reading is done from top to bottom until a the cache key is found. If found, 
 * the cache entries bubbles up into higher priority backends. Writing is done to 
 * all backends from top to bottom.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Stacked extends Base implements Backend
{

        /**
         * The backends array.
         * @var AssocArrayCompare 
         */
        private $_backends;

        /**
         * Constructor.
         * @param array $options The cache options.
         */
        public function __construct($options = [])
        {
                parent::__construct($options, []);

                $this->_backends = new AssocArrayCompare(static function($obj1, $obj2) {
                        return $obj1->getOption('lifetime') - $obj2->getOption('lifetime');
                });
        }

        /**
         * {@inheritdoc}
         */
        public function delete($key)
        {
                foreach ($this->_backends as $name => $backend) {
                        if (!$backend->delete($key)) {
                                throw new RuntimeException("Failed delete from cache backend $name");
                        }
                }

                return true;
        }

        /**
         * {@inheritdoc}
         */
        public function exists($key)
        {
                foreach ($this->_backends as $backend) {
                        if (($result = $backend->exists($key))) {
                                return $result;
                        }
                }
        }

        /**
         * {@inheritdoc}
         */
        public function read($key)
        {
                $missing = [];

                foreach ($this->_backends as $backend) {
                        if (($result = $backend->read($key))) {
                                $this->insert($key, $result, $missing);
                                return $result;
                        } else {
                                $missing[] = $backend;
                        }
                }
        }

        /**
         * {@inheritdoc}
         */
        public function save($key, $value = null, int $lifetime = 0)
        {
                $lifetime = $this->getLifetime($lifetime);
                
                foreach ($this->_backends as $backend) {
                        $backend->save($key, $value, $lifetime);
                }
        }

        /**
         * Polulate cache key/value.
         * 
         * @param string|array $key The cache key.
         * @param string $value The value to cache (only if key is string).
         * @param array $backends The array of backends.
         */
        private function insert($key, $value, $backends)
        {
                foreach ($backends as $backend) {
                        $backend->save($key, $value);
                }
        }

        /**
         * Add cache backend,
         * 
         * @param int $lifetime The lifetime priority.
         * @param string $ident The backend identity.
         * @param Backend $backend The backend object.
         */
        public function addBackend(string $ident, Backend $backend)
        {
                $this->_backends->addObject($ident, $backend);
        }

        /**
         * Remove cache backend.
         * @param string $ident The backend identity.
         */
        public function removeBackend(string $ident)
        {
                $this->_backends->removeObject($ident);
        }

        /**
         * Get cache backend.
         * @param string $ident The backend identity.
         * @return Backend
         */
        public function getBackend(string $ident): Backend
        {
                return $this->_backends->getObject($ident);
        }

        /**
         * Get all backends.
         */
        public function getBackends(): array
        {
                return $this->_backends->getObjects();
        }

}
