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

namespace Batchelor\Queue\Task\Manager;

use Batchelor\Queue\Task\Manager;
use Batchelor\Queue\Task\Manager\Shared\TaskRunner;
use Batchelor\Queue\Task\Runtime;
use RuntimeException;
use Throwable;

/**
 * Process forking task executor.
 * 
 * The current array contains all running processes. The key is the process
 * identity (PID) and the value is the job ID (from job scheduler). 
 * 
 * When calling getChildren() to harvest finished jobs, the returned array will 
 * contain the required data for communicate status back to scheduler.
 *
 * @author Anders Lövgren (Nowise Systems)
 * @see Manager::getChildren()
 */
class Prefork implements Manager
{

        /**
         * The number of running tasks.
         * @var int 
         */
        private $_running;
        /**
         * The number of workers.
         * @var int 
         */
        private $_workers;
        /**
         * The current executing tasks.
         * @var array 
         */
        private $_current;

        /**
         * Constructor.
         * 
         * @param int $workers The number of workers.
         * @throws RuntimeException
         */
        public function __construct(int $workers)
        {
                if (!extension_loaded("pcntl")) {
                        throw new RuntimeException("The pcntl extension is not loaded");
                }

                $this->setWorkers($workers);
        }

        /**
         * {@inheritdoc}
         */
        public function getType(): string
        {
                return "prefork";
        }

        /**
         * {@inheritdoc}
         */
        public function setWorkers(int $number)
        {
                $this->_running = 0;
                $this->_workers = $number;
                $this->_current = [];
                $this->_waiting = [];
        }

        /**
         * {@inheritdoc}
         */
        public function isBusy(): bool
        {
                return $this->_running == $this->_workers;
        }

        /**
         * {@inheritdoc}
         */
        public function isIdle(): bool
        {
                return $this->_running == 0;
        }

        /**
         * {@inheritdoc}
         */
        public function addJob(Runtime $runtime)
        {
                if ($this->isBusy()) {
                        throw new RuntimeException("The manager is busy");
                }

                switch (($pid = pcntl_fork())) {
                        case -1:
                                throw new RuntimeException("Failed fork process");
                        case 0:
                                $this->runJob($runtime);
                        default:
                                $this->setBusy($pid, $runtime);
                }
        }

        /**
         * {@inheritdoc}
         */
        public function getChildren(): array
        {
                $result = [];
                $status = 0;

                foreach ($this->_current as $pid => $job) {
                        switch (pcntl_waitpid($pid, $status, WNOHANG)) {
                                case 0:
                                        break;  // Still running
                                case -1:
                                        throw new RuntimeException("Failed wait on child process $pid");
                                default:
                                        $result[] = [
                                                'code' => pcntl_wexitstatus($status),
                                                'pid'  => $pid,
                                                'job'  => $job
                                        ];
                                        $this->setFree($pid);
                        }
                }

                return $result;
        }

        /**
         * Run single task.
         * 
         * @param Runtime $runtime The task runtime.
         */
        private function runJob(Runtime $runtime)
        {
                try {
                        (new TaskRunner())->runTask($runtime);
                } catch (Throwable $exception) {
                        error_log(print_r($exception, true));
                } finally {
                        exit(0);        // Exit child process
                }
        }

        /**
         * Set process slot as busy.
         * 
         * @param int $pid The process ID.
         * @param Runtime $runtime The job runtime.
         */
        private function setBusy(int $pid, Runtime $runtime)
        {
                $this->_current[$pid] = $runtime->job;
                $this->_running++;
        }

        /**
         * Set process slot as free.
         * 
         * @param int $pid The process ID.
         */
        private function setFree(int $pid)
        {
                unset($this->_current[$pid]);
                $this->_running--;
        }

}
