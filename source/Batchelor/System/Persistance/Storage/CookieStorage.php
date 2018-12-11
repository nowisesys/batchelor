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
use RuntimeException;

/**
 * The cookie service.
 * 
 * Provides persistant storage using cookies. The read() and save() methods is intended
 * to be used with simple strings or already serialized data. For working with objects,
 * use the store() and fetch() methods. When storing obejcts, these are serialized and
 * base64 encoded.
 * 
 * The save()/read() and store()/fetch() works in pair. Mixing them will probably cause
 * an serialize/deserialize error.
 * 
 * The session lifetime is tracked by the cookie. Using lifetime = 0 will create session
 * cookies that expires when browser is closed.
 * 
 * Notice: 
 * 
 * 1. This storage service should only be used before output has been generated unless
 *    output buffering is implemented.
 * 
 * 2. The value set by calling save() or store() can't be read back during the same
 *    request. Values are not cached internal in this class.
 * 
 * 3. Trying to read/fetch an non-existing cookie yields an type error. Use exists()
 *    to check before reading.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class CookieStorage implements StorageService
{

        /**
         * Check if cookie exist.
         * 
         * @param string $name The cookie name.
         * @return bool
         */
        public function exists(string $name): bool
        {
                return filter_has_var(INPUT_COOKIE, $name);
        }

        /**
         * Clear cookie.
         * 
         * @param string $name The cookie name.
         * @param string $path The path on the server (defaults to '/' meaning whole domain).
         */
        public function delete(string $name, string $path = "/")
        {
                if (!setcookie($name, "", time() - 3600, $path)) {
                        throw new RuntimeException("Failed clear $name session cookie");
                }
        }

        /**
         * Set cookie.
         * 
         * Use 0 as lifetime when creating session cookies.
         * 
         * @param string $name The cookie name.
         * @param string $value The cookie value.
         * @param int $lifetime The cookie lifetime.
         * @param string $path The path on the server (defaults to '/' meaning whole domain).
         */
        public function save(string $name, string $value, int $lifetime = 0, string $path = "/")
        {
                if (!setcookie($name, $value, $lifetime, $path)) {
                        throw new RuntimeException("Failed set $name session cookie");
                }
        }

        /**
         * Get cookie value.
         * 
         * @param string $name The cookie name.
         * @return string
         */
        public function read(string $name): string
        {
                return filter_input(INPUT_COOKIE, $name, FILTER_SANITIZE_STRING);
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
                return unserialize(base64_decode($data));
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
                $data = base64_encode(serialize($value));
                $this->save($name, $data, $lifetime);
        }

}
