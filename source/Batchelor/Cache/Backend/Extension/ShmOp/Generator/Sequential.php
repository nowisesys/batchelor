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

use Batchelor\Cache\Backend\Extension\ShmOp\Generator;

/**
 * Sequential number generator.
 * 
 * This class can only be used if generated sequence numbers are tracked so that
 * same generated number are mapped against the segment name. The seed can be used 
 * together with ftok().
 * 
 * <code>
 * $generator = new Sequential(ftok(__FILE__, "0"));
 * $generator->next();  // i.e. 
 * </code>
 * 
 * Whether the same sequence number is generated or not depends strongly on the 
 * code utilizing this class.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Sequential implements Generator
{

        /**
         * The sequence seed:
         * @var int 
         */
        private $_seed;
        /**
         * The last sequence number.
         * @var int 
         */
        private $_last;

        /**
         * Constructor.
         * @param int $seed The sequence seed.
         */
        public function __construct(int $seed = 0)
        {
                $this->_seed = $seed;
                $this->_last = $seed;
        }

        /**
         * Get sequence seed.
         * @return int
         */
        public function getSeed(): int
        {
                return $this->_seed;
        }

        /**
         * Reseed the sequence.
         * @param int $seed The sequence seed.
         */
        public function setSeed(int $seed)
        {
                $this->_seed = $seed;
                $this->_last += $seed;
        }

        /**
         * {@inheritdoc}
         */
        public function next(string $key): int
        {
                return ++$this->_last;
        }

}
