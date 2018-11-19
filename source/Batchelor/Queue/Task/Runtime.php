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

use Batchelor\Logging\Format\DateTime;
use Batchelor\Logging\Logger;
use Batchelor\Logging\Target\Memory;
use Batchelor\Queue\System\SystemDirectory;
use Batchelor\Storage\Directory;
use Batchelor\Storage\File;
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
         * The message logger.
         * @var Logger 
         */
        private $_logger;
        /**
         * The job ID.
         * @var string 
         */
        public $job = 0;
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
         * The root directory.
         * @var string 
         */
        public $result;
        /**
         * This runtime has been cloned.
         * @var bool 
         */
        public $cloned = false;
        /**
         * The job owner.
         * @var Owner 
         */
        public $owner;

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
                $this->job = $job;
                $this->data = $data;
                $this->result = $result;

                $this->owner = new Owner($hostid);

                $this->_logger = $this->useLogger();
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

        /**
         * Get working directory.
         * @return Directory
         */
        public function getWorkDirectory(): Directory
        {
                return (new SystemDirectory($this->owner->hostid))
                        ->getWorkDirectory($this->result);
        }

        /**
         * Get result directory.
         * @return Directory
         */
        public function getResultDirectory(): Directory
        {
                return (new SystemDirectory($this->owner->hostid))
                        ->getResultDirectory($this->result);
        }

        /**
         * Get logfile for this task.
         * @return File
         */
        public function getLogfile(): File
        {
                return $this->getWorkDirectory()
                        ->getFile(
                            sprintf("task-%s.log", $this->data->task)
                );
        }

        /**
         * Get callback for task interaction.
         * @return Callback
         */
        public function getCallback(): Callback
        {
                return new Callback($this);
        }

        /**
         * Get message logger.
         * @return Logger The message logger.
         */
        public function getLogger(): Logger
        {
                return $this->_logger;
        }

        /**
         * Set message logger.
         * 
         * Call this method to replace the default in memory logger with for 
         * example a file logger or syslog.
         * 
         * @param Logger $logger The message logger.
         */
        public function setLogger(Logger $logger)
        {
                $this->_logger = $logger;
        }

        /**
         * Create message logger.
         * @return Memory
         */
        public function useLogger()
        {
                return new Memory([
                        'expand'   => "@datetime@: @message@ (@priority@)",
                        'datetime' => DateTime::FORMAT_HUMAN
                ]);
        }

        /**
         * Get object clone.
         * 
         * Clone this object and assign optional job data. The existing logger 
         * is replaced with new memory logger.
         * 
         * @param JobData $data The job data.
         * @return Runtime
         */
        public function getClone(JobData $data = null): Runtime
        {
                $runtime = clone $this;
                $runtime->data = $data;
                $runtime->cloned = true;
                $runtime->setLogger($runtime->useLogger());

                return $runtime;
        }

}
