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
 * The delete command.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Delete extends Command
{

        /**
         * Constructor.
         * 
         * @param Backend $backend The cache backend.
         * @param array|string $keys The cache keys.
         */
        public function __construct(Backend $backend, $keys)
        {
                parent::__construct(self::getArray($backend, $keys));
        }

        /**
         * Get keys array.
         * 
         * @param Backend $backend The cache backend.
         * @param array|string $keys The keys to delete.
         * @return array
         */
        private static function getArray(Backend $backend, $keys)
        {
                $result = [];

                if (is_string($keys)) {
                        $keys = [$keys];
                }
                foreach ($keys as $key) {
                        $key = $backend->getCacheKey($key);
                        $result[$key] = true;
                }

                return $result;
        }

        /**
         * Get command result.
         * 
         * @param bool $single Retutn single result.
         * @return bool|array
         */
        public function getResult(bool $single)
        {
                if ($single) {
                        return $this->getSuccess();
                } else {
                        return $this->getCompleted();
                }
        }

}
