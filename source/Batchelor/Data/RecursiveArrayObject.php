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

namespace Batchelor\Data;

use ArrayObject;
use InvalidArgumentException;

/**
 * Modified version of etconsilium/php-recursive-array-object with support for 
 * having closures as values.
 */
class RecursiveArrayObject extends ArrayObject
{

        /**
         * Constructor.
         * 
         * @param array $input The config data.
         * @param int $flags The array object flags.
         * @param string $iterator_class The iterator class.
         */
        public function __construct(array $input = null, int $flags = self::ARRAY_AS_PROPS, string $iterator_class = "ArrayIterator")
        {
                foreach ($input as $k => $v) {
                        if (is_array($v)) {
                                $this->offsetSet($k, (new RecursiveArrayObject($v, $flags)));
                        } else {
                                $this->offsetSet($k, $v);
                        }
                }
        }

        public function __set($name, $value)
        {
                $this->offsetSet($name, $value);
        }

        public function __get($name)
        {
                if ($this->offsetExists($name)) {
                        return $this->offsetGet($name);
                } elseif (array_key_exists($name, $this)) {
                        return $this[$name];
                } else {
                        throw new InvalidArgumentException(sprintf('$this have no property `%s`', $name));
                }
        }

        public function __isset($name)
        {
                return array_key_exists($name, $this);
        }

        public function __unset($name)
        {
                unset($this[$name]);
        }

}
