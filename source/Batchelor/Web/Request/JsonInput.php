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

namespace Batchelor\Web\Request;

/**
 * The JSON input data.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
trait JsonInput
{

        /**
         * The JSON input.
         * @var array 
         */
        private $_input;

        /**
         * Get JSON input.
         * @return array
         */
        protected function getInput(): array
        {
                return $this->_input;
        }

        /**
         * Check if JSON input exists.
         * @return bool
         */
        protected function hasInput(): bool
        {
                return isset($this->_input);
        }

        /**
         * Set JSON input.
         */
        private function setInput()
        {
                if (!($this->_input = json_decode(
                    file_get_contents("php://input"), true
                    ))) {
                        $this->_input = [];
                }
        }

}
