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

namespace Batchelor\Queue\Task;

use Batchelor\Queue\System\SystemDirectory;
use Batchelor\Storage\Directory;
use Batchelor\WebService\Types\JobData;
use RuntimeException;

/**
 * The runtime data.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Runtime
{

        /**
         * The job ID.
         * @var string 
         */
        public $job;
        /**
         * The process ID (PID/TID).
         * @var int 
         */
        public $pid;
        /**
         * The job data.
         * @var JobData 
         */
        public $data;
        /**
         * The host ID.
         * @var string 
         */
        public $hostid;
        /**
         * The root directory.
         * @var string 
         */
        public $result;

        /**
         * Constructor.
         * 
         * @param string $job The job ID.
         * @param Jobdata $data The job data.
         * @param string $hostid The hostid.
         * @param string $result The root directory.
         */
        public function __construct(string $job, Jobdata $data, string $hostid, string $result)
        {
                $this->pid = 0;
                $this->job = $job;
                $this->data = $data;
                $this->hostid = $hostid;
                $this->result = $result;
        }

        /**
         * Signal this process.
         * @param int $signal The signal number (i.e. SIGSTOP).
         */
        public function sendSignal(int $signal)
        {
                if (!extension_loaded("posix")) {
                        throw new RuntimeException("The posix extension is not loaded");
                }
                if (!posix_kill(0, $this->pid)) {
                        throw new RuntimeException("Not permitted to signal process $this->pid");
                }
                if (!posix_kill($signal, $this->pid)) {
                        throw new RuntimeException("Failed send signal $signal to process $this->pid");
                }
        }

        public function getWorkDirectory(): Directory
        {
                return (new SystemDirectory($this->hostid))
                        ->getWorkDirectory($this->result);
        }

        public function getResultDirectory(): Directory
        {
                return (new SystemDirectory($this->hostid))
                        ->getResultDirectory($this->result);
        }

        public function getCallback(): Callback
        {
                if (isset($this->_callback)) {
                        return $this->_callback;
                } else {
                        return $this->_callback = new Callback();
                }
        }

}
