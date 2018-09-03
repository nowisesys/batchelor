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

/**
 * The storage interface.
 * 
 * This interface supports methods taking single or multiple keys. The keys are 
 * passed as an array. The backend implementing this interface should be permissive,
 * i.e. deleting missing keys are not an exceptional case and its sufficient to 
 * return false in this case.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
interface Storage
{

        /**
         * Check if cache key(s) exist.
         * 
         * <code>
         * // 
         * // Check single key (will return bool).
         * // 
         * $backend->exist("key1");
         * 
         * // 
         * // Check array of keys (will return i.e. array("key1" => true) if 
         * // key1 exist but key2 is missing).
         * // 
         * $backend->exist(["key1", "key2"]);
         * </code>
         * 
         * @param string|array $key The cache key.
         * @param int $lifetime The cache entry lifetime.
         * @return bool|array
         */
        function exists($key, int $lifetime = 0);

        /**
         * Save value(s) to cache.
         * 
         * <code>
         * $backend->save("key1", "value1");                            // Store single
         * $backend->save(["key1" => "value1", "key2" => "value2"]);    // Store array
         * </code>
         * 
         * @param string|array $key The cache key.
         * @param string $value The value to cache (only if key is string).
         * @param int $lifetime The cache entry lifetime.
         */
        function save($key, $value = null, int $lifetime = 0);

        /**
         * Read value(s) having key(s) from cache.
         * 
         * <code>
         * $backend->read("key1");              // Get single value
         * $backend->read(["key1", "key2"]);    // Get multiple values.
         * </code>
         * 
         * When used with an array of keys, the returned array will contain an
         * associated array having the cache keys as names.
         * 
         * @param string|array $key The cache key.
         * @param int $lifetime The cache entry lifetime.
         * @return mixed
         */
        function read($key, int $lifetime = 0);

        /**
         * Delete key(s) from cache.
         * 
         * <code>
         * $backend->delete("key1");            // Delete single key
         * $backend->delete(["key1", "key2"]);  // Delete multiple keys
         * </code>
         * 
         * @param string|array $key The cache key.
         */
        function delete($key);
}
