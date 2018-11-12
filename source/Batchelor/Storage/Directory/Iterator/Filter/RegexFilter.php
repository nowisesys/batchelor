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

namespace Batchelor\Storage\Directory\Iterator\Filter;

use FilterIterator;
use Iterator;

/**
 * Regex filtering of iterator.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class RegexFilter extends FilterIterator
{

        /**
         * The regex pattern.
         * @var string 
         */
        private $_filter;

        /**
         * Constructor.
         * 
         * @param Iterator $iterator The inner iterator.
         * @param string $filter The regex pattern.
         */
        public function __construct($iterator, string $filter)
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
                return preg_match($this->_filter, $this->current()->getFilename());
        }

}
