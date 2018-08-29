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

namespace Batchelor\System\Directory\Iterator\Filter;

use FilterIterator;
use Iterator;

/**
 * Array filtering of iterator.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class ArrayFilter extends FilterIterator
{

        /**
         * The filename array.
         * @var array 
         */
        private $_filter;

        /**
         * Constructor.
         * 
         * @param Iterator $iterator The inner iterator.
         * @param array $filter The filename array.
         */
        public function __construct($iterator, array $filter)
        {
                parent::__construct($iterator);
                $this->_filter = $filter;
        }

        /**
         * Check if pattern match.
         * @return bool
         */
        public function accept(): bool
        {
                return in_array($this->current()->getFilename(), $this->_filter, true);
        }

}
