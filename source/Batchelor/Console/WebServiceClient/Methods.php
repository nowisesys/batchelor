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

namespace Batchelor\Console\WebServiceClient;

/**
 * Service method information.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Methods
{

        /**
         * Complex types.
         * @var array 
         */
        private $_types = [];
        /**
         * The web methods.
         * @var array 
         */
        private $_methods = [];

        /**
         * Add complex type.
         * @param string $name The type name.
         * @param object $data The complex type.
         */
        public function addType($name, $data)
        {
                $this->_types[$name] = $data;
        }

        /**
         * Get complex type.
         * @param string $name The type name.
         * @return object
         */
        public function getType($name)
        {
                return $this->_types[$name];
        }

        /**
         * Get all complex types.
         * @return array
         */
        public function getTypes()
        {
                return $this->_types;
        }

        /**
         * Add service method.
         * @param string $name The method name.
         * @param array $params The method params.
         */
        public function addMethod($name, $params)
        {
                $this->_methods[$name] = $params;
        }

        /**
         * Get all method names.
         * @return array
         */
        public function getMethods()
        {
                return array_keys($this->_methods);
        }

        /**
         * Get params for method.
         * @param string $method The method name.
         * @return array
         */
        public function getParams($method)
        {
                return $this->_methods[$method];
        }

}
