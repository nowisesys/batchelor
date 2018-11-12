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

namespace Batchelor\WebService\Types;

use InvalidArgumentException;
use JsonSerializable;
use ReflectionClass;

/**
 * The enum class.
 *
 * Classes that represent enum types can derive from this class to provide basic 
 * enum behavior and type safety. Constant values can be any basic type (i.e. int, 
 * float or string).
 *
 * <code>
 * class WeekDay extends Enum
 * {
 *      const MONDAY = 'monday';
 *      const TUESDAY = 'tuesday';
 *              // ...
 *
 *      public function __construct($value)
 *      {
 *              parent::__construct($value, __CLASS__);
 *      }
 * }
 *
 * $today = new WeekDay('fredag');              // Throws
 * $today = new WeekDay('friday');              // Explicit create enum object
 * $today = WeekDay::FRIDAY();                  // implicit create enum object
 *
 * if ($today->hasValue(WeekDay::FRIDAY)) {     // Compare values
 *      echo "It's friday!!\n";
 * }
 * if ($today->getValue() == WeekDay::FRIDAY) { // Compare values
 *      echo "It's friday!!\n";
 * }
 * if ($today() == WeekDay::FRIDAY) {           // Compare values
 *      echo "It's friday!!\n";
 * }
 * if ($today == new WeekDay('friday')) {       // Compare objects
 *      echo "It's friday!!\n";
 * }
 * if ($today == WeekDay::FRIDAY()) {           // Compare objects
 *      echo "It's friday!!\n";
 * }
 *
 * $today->hasConstant('friday');               // True
 * </code>
 *
 * @author Anders Lövgren (Nowise Systems)
 */
abstract class EnumType implements JsonSerializable
{

        /**
         * The enum value.
         * @var string
         */
        public $value;
        /**
         * The inheriting class.
         * @var string
         */
        private $class;

        /**
         * Constructor.
         *
         * @param mixed $value The enum value.
         * @param string $class The inheriting class.
         */
        protected function __construct($value, string $class)
        {
                $this->value = $value;
                $this->class = $class;

                if (!$this->hasConstant($value)) {
                        throw new InvalidArgumentException("Invalid enum value $value");
                }
        }

        /**
         * Get the enum value using () invocation.
         * @return mixed
         */
        public function __invoke()
        {
                return $this->value;
        }

        public static function __callStatic($name, $arguments)
        {
                $class = new ReflectionClass(get_called_class());
                $constants = $class->getConstants();

                $class = get_called_class();
                return new $class($constants[$name]);
        }

        /**
         * Compare object against value.
         * 
         * Return true if this object has value. If strict (default), then the
         * value type is also compared.
         * 
         * @param mixed $value The value to compare against.
         * @param bool $strict Use strict comparision.
         * @return bool
         */
        public function hasValue($value, bool $strict = true)
        {
                if ($strict) {
                        return $this->value === $value;
                } else {
                        return $this->value === $value;
                }
        }

        /**
         * Get enum value.
         * @return mixed
         */
        public function getValue()
        {
                return $this->getConstant($this->value)['val'];
        }

        /**
         * Get enum name.
         * @return string
         */
        public function getName()
        {
                return $this->getConstant($this->value)['key'];
        }

        /**
         * Get all defined constants.
         * @return array
         */
        public function getConstants()
        {
                $class = new ReflectionClass($this->class);
                return $class->getConstants();
        }

        /**
         * Check if enum type defines constant value.
         * @param mixed $value The enum value.
         * @return bool
         */
        public function hasConstant($value)
        {
                return $this->getConstant($value)['val'] != null;
        }

        /**
         * Get constant pair for enum value.
         * @param mixed $value The enum value.
         * @return array
         */
        private function getConstant($value)
        {
                $constants = $this->getConstants();

                foreach ($constants as $key => $val) {
                        if ($val == $value) {
                                return ['key' => $key, 'val' => $val];
                        }
                }

                return ['key' => null, 'val' => null];
        }

        /**
         * Get data for JSON encoding.
         * @return string
         */
        public function jsonSerialize()
        {
                return $this->value;
        }

}
