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

/**
 * The command base class.
 * 
 * <code>
 * // 
 * // The return value will replace value in keys array.
 * // 
 * $command->applyOne(function($key, $val) {
 *      return true;
 * });
 * 
 * // 
 * // The returned array will replace the keys array.
 * // 
 * $command->applyAll(function($keys) {
 *      return $keys;
 * });
 * </code>
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
abstract class Command
{

        /**
         * The cache keys.
         * @var array 
         */
        private $_keys;

        /**
         * Constructor.
         * @param array $keys The cache keys.
         */
        protected function __construct(array $keys)
        {
                $this->_keys = $keys;
        }

        /**
         * Apply all keys at once to callable.
         */
        public function applyAll(callable $callable)
        {
                $this->_keys = $callable($this->_keys);
        }

        /**
         * Apply single key/value to callable.
         */
        public function applyOne(callable $callable)
        {
                foreach ($this->_keys as $key => $val) {
                        $this->_keys[$key] = $callable($key, $val);
                }
        }

        /**
         * Get results.
         * @return array The results array.
         */
        public function getResults(): array
        {
                return $this->_keys;
        }

        /**
         * Get all completed operations.
         * 
         * Return an array containing cache keys with value true. The keys are
         * all cache entries were last operation completed successful.
         * @return array
         */
        public function getCompleted(): array
        {
                return array_filter($this->_keys);
        }

        /**
         * Check if last call was successful.
         * 
         * This method will only report true if all sub operation of last
         * command completed successful.
         * 
         * @return bool
         */
        public function getSuccess(): bool
        {
                return count($this->_keys) == count(array_filter($this->_keys));
        }

}
