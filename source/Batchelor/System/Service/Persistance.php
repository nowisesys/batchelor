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

use Batchelor\System\Persistance\Storage\CookieStorage;
use Batchelor\System\Persistance\Storage\SessionStorage;
use Batchelor\System\Persistance\Storage\StorageService;
use InvalidArgumentException;

/**
 * The persistance service.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Persistance implements StorageService
{

        /**
         * The storage backend.
         * @var StorageService 
         */
        private $_storage;

        /**
         * Constructor.
         * 
         * @param string $backend The storage backend type.
         * @throws InvalidArgumentException
         */
        public function __construct($backend = 'cookie')
        {
                switch ($backend) {
                        case 'cookie':
                                $this->_storage = new CookieStorage();
                                break;
                        case 'session':
                                $this->_storage = new SessionStorage();
                                break;
                        default:
                                throw new InvalidArgumentException("Unknown storage backend $backend for persistance service");
                }
        }

        /**
         * Set storage backend.
         * @param StorageService $storage The storage backend.
         */
        public function setBackend(StorageService $storage)
        {
                $this->_storage = $storage;
        }

        /**
         * Delete an stored value/object.
         * 
         * @param string $name The key name.
         */
        public function delete(string $name)
        {
                $this->_storage->delete($name);
        }

        /**
         * Check if key exists.
         * 
         * @param string $name The key name.
         * @return bool 
         */
        public function exists(string $name): bool
        {
                return $this->_storage->exists($name);
        }

        /**
         * Read value from storage.
         * 
         * @param string $name The key name.
         * @return string
         */
        public function read(string $name): string
        {
                return $this->_storage->read($name);
        }

        /**
         * Save value to storage.
         * 
         * @param string $name The key name.
         * @param string $value The value to store.
         * @param int $lifetime The value lifetime.
         */
        public function save(string $name, string $value, int $lifetime = 0)
        {
                $this->_storage->save($name, $value, $lifetime);
        }

        /**
         * Fetch serialized data from storage.
         * 
         * @param string $name The key name.
         * @return mixed The stored data (i.e. an array or object).
         */
        public function fetch(string $name)
        {
                return $this->_storage->fetch($name);
        }

        /**
         * Store serializable data.
         * 
         * @param string $name The key name.
         * @param mixed $value The data to store.
         * @param int $lifetime The data lifetime.
         */
        function store(string $name, $value, int $lifetime = 0)
        {
                $this->_storage->store($name, $value, $lifetime);
        }

}
