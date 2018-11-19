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

use Batchelor\Logging\Logger;
use Batchelor\Queue\Task\Execute\Capture;
use Batchelor\Queue\Task\Execute\Process;
use Batchelor\Queue\Task\Execute\Selectable;
use Batchelor\Queue\Task\Execute\Spawner;
use Batchelor\Queue\Task\Execute\Status;
use Batchelor\Queue\Task\Manager\Shared\TaskRunner;
use Batchelor\Web\Download;
use Batchelor\WebService\Types\JobData;
use Batchelor\WebService\Types\JobState;

/**
 * The task interaction class.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Callback implements Interaction
{

        /**
         * The scheduler object.
         * @var Scheduler 
         */
        private $_scheduler;
        /**
         * The task runtime.
         * @var Runtime 
         */
        private $_runtime;

        /**
         * Constructor.
         * @param Runtime $runtime The task runtime.
         */
        public function __construct(Runtime $runtime)
        {
                $this->_scheduler = new Scheduler();
                $this->_runtime = $runtime;
        }

        /**
         * {@inheritdoc}
         */
        public function setStatus(JobState $state)
        {
                $this->onStatusChanged($state);
        }

        /**
         * {@inheritdoc}
         */
        public function newTask(JobData $data)
        {
                $this->onTaskPush($data);
        }

        /**
         * {@inheritdoc}
         */
        public function getLogger(): Logger
        {
                return $this->_runtime->getLogger();
        }

        /**
         * {@inheritdoc}
         */
        public function setLogger(Logger $logger)
        {
                $this->_runtime->setLogger($logger);
        }

        /**
         * {@inheritdoc}
         */
        public function runCommand(string $cmd, array $env = null, string $cwd = null): Status
        {
                return Capture::create(
                        $this->_runtime->getLogger(), $cmd, $env, $cwd
                    )->execute();
        }

        /**
         * {@inheritdoc}
         */
        public function runProcess(Selectable $command): Process
        {
                return (new Spawner($command))->open();
        }

        /**
         * {@inheritdoc}
         */
        public function getDownloader(string $url): Download
        {
                return new Download($url);
        }

        /**
         * Called on set status.
         */
        protected function onStatusChanged(JobState $state)
        {
                if ($this->_runtime->cloned == false) {
                        $this->_scheduler->setFinished($this->_runtime->job, $state);
                } elseif ($state->isGood() == false) {
                        $this->_scheduler->setFinished($this->_runtime->job, $state);
                }
        }

        /**
         * Called on new task.
         */
        protected function onTaskPush(JobData $data)
        {
                $this->_scheduler->pushJob($this->_runtime->hostid, $data);
        }

        /**
         * {@inheritdoc}
         */
        public function runTask(JobData $data)
        {
                (new TaskRunner())
                    ->runTask(
                        $this->_runtime->getClone($data)
                );
        }

        /**
         * {@inheritdoc}
         */
        public function getOwner(): Owner
        {
                return $this->_runtime->owner;
        }

}
