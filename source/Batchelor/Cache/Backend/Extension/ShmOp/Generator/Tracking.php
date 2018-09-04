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

namespace Batchelor\Cache\Backend\Extension\ShmOp\Generator;

/**
 * Tracking number generator.
 * 
 * Extends the sequential number generator by keeping track of generated numbers
 * so next request for the same segment key returns the same result. 
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Tracking extends Sequential
{

        /**
         * The tracker numbers.
         * @var array 
         */
        private $_tracked = [];

        /**
         * {@inheritdoc}
         */
        public function next(string $key): int
        {
                if (isset($this->_tracked[$key])) {
                        return $this->_tracked[$key];
                } else {
                        return $this->_tracked[$key] = parent::next($key);
                }
        }

}
