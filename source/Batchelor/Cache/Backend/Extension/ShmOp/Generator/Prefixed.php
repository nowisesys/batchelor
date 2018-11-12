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
 * Prefixed number generator.
 * 
 * Encapsulate a number generator and add common prefix on generated sequence
 * numbers. This is basically useful for easy identify your keys in the listing 
 * i.e. by the ipcs command.
 *
 * <code>
 * // 
 * // Wrap a sequence number generator:
 * // 
 * $generator = new Prefix(new Sequential());
 * $generator = new Prefix(new Sequential(), 20000);
 * $generator = new Prefix(new Sequential(), 5000, 2500);
 * 
 * // 
 * // Wrap a hashing number generator:
 * // 
 * $generator = new Prefix(new Sequential(), 40000, 20000);
 * </code>
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Prefixed implements Generator
{

        /**
         * The default prefix.
         */
        const PREFIX = 20000;
        /**
         * The default modulo.
         */
        const MODULO = 10000;

        /**
         * The wrapped generator.
         * @var Generator 
         */
        private $_generator;
        /**
         * The modulo number.
         * @var int 
         */
        private $_modulo;
        /**
         * The prefix number.
         * @var int 
         */
        private $_prefix;

        /**
         * Constructor.
         * 
         * The modulo number can be chosen as the max number of hashed numbers
         * and the prefix should be a number greater than modulo.
         * 
         * @param int $prefix The prefix number.
         * @param int $modulo The modulo number.
         */
        public function __construct(Generator $generator, int $prefix = self::PREFIX, int $modulo = self::MODULO)
        {
                $this->_generator = $generator;

                $this->_prefix = $prefix;
                $this->_modulo = $modulo;
        }

        /**
         * Get wrapped generator.
         * @return Generator 
         */
        public function getGenerator(): Generator
        {
                return $this->_generator;
        }

        /**
         * Set wrapped generator.
         * @param Generator $generator The sequence generator.
         */
        public function setGenerator(Generator $generator)
        {
                $this->_generator = $generator;
        }

        /**
         * {@inheritdoc}
         */
        public function next(string $key): int
        {
                return
                    $this->_prefix +
                    $this->_generator->next($key) %
                    $this->_modulo;
        }

}
