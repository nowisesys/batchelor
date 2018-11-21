<?php

/*
 * Copyright (C) 2018 Anders Lövgren (Nowise Systems).
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Batchelor\Web\Web\Request;

/**
 * Property bag for request options.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
trait Options
{

        /**
         * The options property bag.
         * @var array 
         */
        private $_options = [];

        /**
         * Set request options.
         * 
         * @param array $options The request options.
         */
        protected function setOptions(array $options)
        {
                $this->_options = $options;
        }

        /**
         * Add request options.
         * 
         * Merge existing options with supplied options. The supplied options 
         * will overwrite any existing options with same key.
         * 
         * @param array $options The request options.
         */
        protected function addOptions(array $options)
        {
                $this->_options = array_merge($this->_options, $options);
        }

        /**
         * Set request option.
         * 
         * @param string $name The option name.
         * @param mixed $value The option value.
         */
        protected function setOption(string $name, $value)
        {
                $this->_options[$name] = $value;
        }

        /**
         * Check if request option is set.
         * 
         * @param string $name The option name.
         * @return bool
         */
        protected function hasOption(string $name): bool
        {
                return isset($this->_options[$name]);
        }

        /**
         * Get request option.
         * 
         * @param string $name The option name.
         * @param mixed $default The default value.
         * @return mixed
         */
        protected function getOption(string $name, $default = false)
        {
                if (isset($this->_options[$name])) {
                        return $this->_options[$name];
                } else {
                        return $default;
                }
        }

        /**
         * Get all request options.
         * @return array 
         */
        protected function getOptions(): array
        {
                return $this->_options;
        }

        /**
         * Check if options are empty.
         * @return bool
         */
        protected function hasOptions(): bool
        {
                return !empty($this->_options);
        }

}
