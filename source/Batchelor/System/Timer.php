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

namespace Batchelor\System;

/**
 * Simple measure class.
 * 
 * All time values UNIX timestamps as float values with 
 * micro second precision.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Timer
{

        /**
         * The start time.
         * @var float 
         */
        private $_stime;
        /**
         * The end time .
         * @var float 
         */
        private $_etime;
        /**
         * Accumulated time.
         * @var float 
         */
        private $_tsacc;

        /**
         * Constructor.
         */
        public function __construct()
        {
                $this->_stime = microtime(true);
                $this->_tsacc = 0;
        }

        /**
         * Restart timer.
         */
        public function start()
        {
                $this->_stime = microtime(true);
        }

        /**
         * Stop this timer.
         */
        public function stop()
        {
                $this->_etime = microtime(true);
        }

        /**
         * Set accumulated time since start.
         */
        public function accumulate()
        {
                $this->_tsacc += microtime(true) - $this->_stime;
        }

        /**
         * Get time elapsed.
         * 
         * If accumulate() has been called, then the elapsed time returned will 
         * include total accumulated time in addition to last measure.
         * 
         * @return float
         */
        public function elapsed(): float
        {
                return ($this->_etime - $this->_stime) + $this->_tsacc;
        }

        /**
         * Reset timers and accumulated time.
         */
        public function reset()
        {
                $this->_stime = $this->_etime = $this->_tsacc = 0;
        }

}
