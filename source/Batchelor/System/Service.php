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

namespace Batchelor\System;

use ReflectionClass;

/**
 * System service wapper.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Service
{

        /**
         * The wrapped class.
         * @var string 
         */
        private $_type;
        /**
         * Optional arguments for constructor.
         * @var array 
         */
        private $_args;

        /**
         * Constructor.
         * @param string $type The wrapped class.
         * @param string $args Optional arguments for constructor.
         */
        public function __construct($type, $args = [])
        {
                $this->_type = $type;
                $this->_args = $args;
        }

        /**
         * Create new instance.
         * @return object
         */
        public function getObject()
        {
                return (new ReflectionClass($this->_type))
                        ->newInstanceArgs($this->_args);
        }

}
