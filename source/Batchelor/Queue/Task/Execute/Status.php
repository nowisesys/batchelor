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

namespace Batchelor\Queue\Task\Execute;

/**
 * The process status.
 * 
 * Fancy representation of proc_get_status() output. Initialize either with an
 * process resource or an array of data.
 *
 * @property-read string $command The command string.
 * @property-read int $pid The process ID.
 * @property-read bool $running Is true if the process is still running, false if it has terminated.
 * @property-read bool $signaled Is true if the child process has been terminated by an uncaught signal.
 * @property-read bool $stopped Is true if the child process has been stopped by a signal.
 * @property-read int $exitcode The exit code returned by the process.
 * @property-read int $termsig The number of the signal that caused the child process to terminate its execution (only meaningful if signaled is true).
 * @property-read int $stopsig The number of the signal that caused the child process to stop its execution (only meaningful if stopped is true).
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Status
{

        /**
         * The process status.
         * @var array 
         */
        private $_data;

        /**
         * Constructor
         * @param resource|array $process The process handle or status data.
         */
        public function __construct($process)
        {
                if (is_resource($process) && get_resource_type($process) == 'process') {
                        $this->_data = proc_get_status($process);
                }
                if (is_array($process)) {
                        $this->_data = $process;
                }
        }

        public function __get($name)
        {
                if (isset($this->_data[$name])) {
                        return $this->_data[$name];
                }
        }

}
