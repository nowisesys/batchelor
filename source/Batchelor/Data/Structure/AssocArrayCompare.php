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

/**
 * Ordered object array (heap).
 * 
 * This is concrete implementation of the abstract parent class were the compare
 * method is implemented to call the defined compare method. Otherwise these two
 * classes is the same.
 * 
 * <code>
 * // 
 * // Use static closure to exclude $this:
 * // 
 * $heap = new AssocArrayCompare(static function($obj1, $obj2) {
 *      return $obj1->getValue() - $obj2->getValue();
 * });
 * </code>
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class AssocArrayCompare extends AssocArrayHeap
{

        /**
         * The compare function.
         * @var callable
         */
        private $_compare;

        /**
         * Constructor.
         * @param callable $compare The compare function.
         */
        public function __construct(callable $compare)
        {
                $this->_compare = $compare;
        }

        /**
         * Set compare function.
         * @param callable $compare The compare function.
         */
        public function setCompare(callable $compare)
        {
                $this->_compare = $compare;
        }

        /**
         * Compare two objects using compare function.
         * 
         * @param mixed $object1 The right side object.
         * @param mixed $object2 The left side object.
         * @return int 
         */
        protected function compare($object1, $object2)
        {
                return ($compare = $this->_compare)($object1, $object2);
        }

}
