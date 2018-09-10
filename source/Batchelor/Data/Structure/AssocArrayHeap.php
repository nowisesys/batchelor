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

namespace Batchelor\Data\Structure;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * Ordered object array (heap).
 * 
 * Derive from this class and implement the abstract method compare(). The compare
 * method should return an int less, equal or greater than zero.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
abstract class AssocArrayHeap implements IteratorAggregate
{

        /**
         * The array of objects.
         * @var array 
         */
        private $_objects = [];

        /**
         * The object compare function.
         * 
         * @param mixed $object1 The right side object.
         * @param mixed $object2 The left side object.
         * @return int 
         */
        protected abstract function compare($object1, $object2);

        /**
         * Add object to heap.
         * 
         * @param string $key The object key.
         * @param mixed $object The object value.
         * @param bool $sort Keep heap sorted.
         */
        public function addObject(string $key, $object, bool $sort = true)
        {
                $this->_objects[$key] = $object;

                if ($sort) {
                        $this->setSorted();
                }
        }

        /**
         * Remove object from heap.
         * 
         * @param string $key The object key.
         * @param bool $sort Keep heap sorted.
         */
        public function removeObject(string $key, bool $sort = true)
        {
                if (isset($this->_objects[$key])) {
                        unset($this->_objects[$key]);
                }
                if ($sort) {
                        $this->setSorted();
                }
        }

        /**
         * Check if heap is empty.
         * @return bool
         */
        public function isEmpty(): bool
        {
                return count($this->_objects) == 0;
        }

        /**
         * Check if object key exists.
         * @param string $key The object key.
         * @return bool
         */
        public function hasObject(string $key): bool
        {
                return isset($this->_objects[$key]);
        }

        /**
         * Get object by key.
         * @param string $key The object key.
         * @return mixed
         */
        public function getObject(string $key)
        {
                return $this->_objects[$key];
        }

        /**
         * Get all objects.
         * @return array
         */
        public function getObjects(): array
        {
                return $this->_objects;
        }

        /**
         * Set heap sorted.
         */
        public function setSorted()
        {
                uasort($this->_objects, array($this, 'compare'));
        }

        /**
         * Get array iterator.
         * @return Traversable
         */
        public function getIterator(): Traversable
        {
                return new ArrayIterator($this->_objects);
        }

}
