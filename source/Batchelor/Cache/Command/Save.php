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

namespace Batchelor\Cache\Command;

use Batchelor\Cache\Backend;

/**
 * The save command.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Save extends Command
{

        /**
         * Constructor.
         * 
         * @param Backend $backend The cache backend.
         * @param array|string $keys The cache keys.
         * @param string $value The cache value.
         */
        public function __construct($backend, $keys, $value)
        {
                parent::__construct(self::getArray($backend, $keys, $value));
        }

        /**
         * Get keys array.
         * 
         * @param Backend $backend The cache backend.
         * @param array|string $keys The keys to delete.
         * @param string $value The key value.
         * @return array
         */
        private static function getArray($backend, $keys, $value)
        {
                $result = [];

                if (is_string($keys)) {
                        $keys = [$keys => $value];
                }
                foreach ($keys as $key => $val) {
                        $key = $backend->getCacheKey($key);
                        $result[$key] = $val;
                }

                return $result;
        }

}
