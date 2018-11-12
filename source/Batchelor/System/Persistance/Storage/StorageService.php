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

/**
 * Interface for persistance services.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
interface StorageService
{

        /**
         * Check if key exists.
         * 
         * @param string $name The key name.
         * @return bool 
         */
        function exists(string $name): bool;

        /**
         * Delete an stored value/object.
         * 
         * @param string $name The key name.
         */
        function delete(string $name);

        /**
         * Save value to storage.
         * 
         * @param string $name The key name.
         * @param string $value The value to store.
         * @param int $lifetime The value lifetime.
         */
        function save(string $name, string $value, int $lifetime = 0);

        /**
         * Read value from storage.
         * 
         * @param string $name The key name.
         * @return string
         */
        function read(string $name): string;

        /**
         * Fetch serialized data from storage.
         * 
         * @param string $name The key name.
         * @return mixed The stored data (i.e. an array or object).
         */
        function fetch(string $name);

        /**
         * Store serializable data.
         * 
         * @param string $name The key name.
         * @param mixed $value The data to store.
         * @param int $lifetime The data lifetime.
         */
        function store(string $name, $value, int $lifetime = 0);
}
