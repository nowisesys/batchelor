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

namespace Batchelor\System\Persistance\Storage;

use Batchelor\System\Persistance\Storage\StorageService;

/**
 * The session servie.
 * 
 * Provides persistant storage using sessions. The read() and save() methods is intended
 * to be used with simple strings or already serialized data. For working with objects,
 * use the store() and fetch() methods.
 * 
 * The save()/read() and store()/fetch() works in pair. Mixing them will probably cause
 * an serialize/deserialize error.
 * 
 * The session lifetime is tracked by the session storage. Calling read() with an existing
 * key, but where the lifetime has expired will return null. Using lifetime = 0 means that 
 * the value/object expires when session ends.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class SessionStorage implements StorageService
{

        /**
         * Constructor.
         */
        public function __construct()
        {
                if (session_status() !== PHP_SESSION_ACTIVE) {
                        if (session_status() === PHP_SESSION_DISABLED) {
                                trigger_error("Session handling are disabled");
                        }
                        if (session_status() === PHP_SESSION_NONE || session_start() === false) {
                                trigger_error("Session handling could not be started");
                        }
                }
        }

        /**
         * Check if session data exist.
         * 
         * @param string $name The session data name.
         * @return bool
         */
        public function exists(string $name): bool
        {
                if (session_status() === PHP_SESSION_ACTIVE) {
                        return isset($_SESSION[$name]);
                }
        }

        /**
         * Clear session data.
         * 
         * @param string $name The session data name.
         */
        public function delete(string $name)
        {
                if (session_status() === PHP_SESSION_ACTIVE) {
                        unset($_SESSION[$name]);
                }
        }

        /**
         * Set session data.
         * 
         * @param string $name The session data name.
         * @param string $value The session data value.
         * @param int $lifetime The session data lifetime.
         */
        public function save(string $name, string $value, int $lifetime = 0)
        {
                if (session_status() === PHP_SESSION_ACTIVE) {
                        $_SESSION[$name] = [
                                'value'    => $value,
                                'lifetime' => $lifetime
                        ];
                }
        }

        /**
         * Get session data.
         * 
         * @param string $name The session data name.
         * @return string
         */
        public function read(string $name): string
        {
                if (session_status() === PHP_SESSION_ACTIVE) {
                        if (($data = $_SESSION[$name])) {
                                if ($data['lifetime'] == 0 ||
                                    $data['lifetime'] > time()) {
                                        return $data['value'];
                                }
                        }
                }
        }

        /**
         * Fetch serialized data from storage.
         * 
         * @param string $name The key name.
         * @return mixed The stored data (i.e. an array or object).
         */
        public function fetch(string $name)
        {
                $data = $this->read($name);
                return unserialize($data);
        }

        /**
         * Store serializable data.
         * 
         * @param string $name The key name.
         * @param mixed $value The data to store.
         * @param int $lifetime The data lifetime.
         */
        public function store(string $name, $value, int $lifetime = 0)
        {
                $data = serialize($value);
                $this->save($name, $data, $lifetime);
        }

}
