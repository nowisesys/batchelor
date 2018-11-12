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
use Batchelor\Queue\Task\Manager\Prefork\Delegate;
use Batchelor\Queue\Task\Manager\Prefork\Scratch;
use Batchelor\Queue\Task\Runtime;
use RuntimeException;

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
         * The number of workers.
         * @var int 
         */
        private $_workers;

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
                $this->_workers = $number;
        }

        /**
         * {@inheritdoc}
         */
        public function isBusy(): bool
        {
                return (new Scratch())->numRunning() >= $this->_workers;
        }

        /**
         * {@inheritdoc}
         */
        public function isIdle(): bool
        {
                return (new Scratch())->numRunning() == 0;
        }

        /**
         * {@inheritdoc}
         */
        public function getRunning(): int
        {
                return (new Scratch())->numRunning();
        }

        /**
         * {@inheritdoc}
         */
        public function addJob(Runtime $runtime)
        {
                switch (($pid = pcntl_fork())) {
                        case -1:
                                throw new RuntimeException("Failed fork process");
                        case 0:
                                (new Delegate($runtime, $this))->run();
                                exit(0);
                        default:
                                usleep(100000);         // Don't spawn too fast
                }
        }

        /**
         * {@inheritdoc}
         */
        public function hasFinished(): bool
        {
                return (new Scratch())->hasFinished();
        }

        /**
         * {@inheritdoc}
         */
        public function getFinished(): array
        {
                $this->setFinished();

                try {
                        return (new Scratch())->getFinished();
                } finally {
                        (new Scratch())->setFinished([]);
                }
        }

        /**
         * {@inheritdoc}
         */
        public function onFinished(array $data)
        {
                (new Scratch())->addFinished($data);
                (new Scratch())->removeRunning($data['pid']);
        }

        /**
         * {@inheritdoc}
         */
        public function onStarting(array $data)
        {
                (new Scratch())->addRunning($data['pid'], $data['job']);
        }

        /**
         * Collect finished child processes.
         * @throws RuntimeException
         */
        private function setFinished(int $status = 0)
        {
                foreach ((new Scratch())->getRunning() as $pid => $job) {
                        switch (pcntl_waitpid($pid, $status, WNOHANG)) {
                                case 0:
                                        break;  // Still running
                                case -1:
                                        throw new RuntimeException("Failed wait on child process $pid");
                                default:
                                        $this->onFinished([
                                                'code' => pcntl_wexitstatus($status),
                                                'pid'  => $pid,
                                                'job'  => $job
                                        ]);
                        }
                }
        }

}
