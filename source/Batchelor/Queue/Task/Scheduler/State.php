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

namespace Batchelor\Queue\Task\Scheduler;

use Batchelor\WebService\Types\JobIdentity;
use Batchelor\WebService\Types\JobState;
use Batchelor\WebService\Types\JobStatus;
use Batchelor\WebService\Types\JobSubmit;
use Batchelor\WebService\Types\QueuedJob;
use DateTime;

/**
 * The job state.
 * 
 * Simple class representing an state queue item.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class State
{

        /**
         * The host ID.
         * @var string 
         */
        public $hostid;
        /**
         * The result directory.
         * @var string 
         */
        public $result;
        /**
         * The current task.
         * @var string 
         */
        public $task;
        /**
         * The job status.
         * @var JobStatus 
         */
        public $status;
        /**
         * The submitted job (gecos).
         * @var JobSubmit 
         */
        public $submit;

        /**
         * Constructor.
         * 
         * @param string $hostid The host ID.
         * @param string $task The current task.
         * @param string $name The job name (optional).
         */
        public function __construct(string $hostid, string $task, string $name = null)
        {
                $this->hostid = $hostid;
                $this->result = sprintf("%d%d", time(), rand(1000, 9999));

                $this->status = new JobStatus(new DateTime(), JobState::PENDING());
                $this->submit = new JobSubmit($task, $name);

                $this->task = $task;
        }

        /**
         * Get queued job.
         * 
         * @param string $jobid The job ID.
         * @return JobIdentity
         */
        public function getJobIdentity(string $jobid): JobIdentity
        {
                return new JobIdentity($jobid, $this->result);
        }

        /**
         * Get job status.
         * @return JobStatus
         */
        public function getJobStatus(): JobStatus
        {
                return $this->status;
        }

        private function getJobSubmit(): JobSubmit
        {
                return $this->submit;
        }

        /**
         * Get queued job.
         * @param string $jobid The job ID.
         */
        public function getQueuedJob(string $jobid): QueuedJob
        {
                return new QueuedJob(
                    $this->getJobIdentity($jobid), $this->getJobStatus(), $this->getJobSubmit()
                );
        }

        /**
         * Set state on object.
         * 
         * Has some side effects, for example sets the started time if state is
         * running. When state is restart, the object is reset to initial state
         * while keeping hostid and result.
         * 
         * @param JobState $state
         */
        public function setState(JobState $state)
        {
                switch ($state->getValue()) {
                        case JobState::RESTART:
                                $this->status->state = $state;
                                $this->status->queued = new DateTime();
                                $this->status->started = null;
                                $this->status->finished = null;
                                break;
                        case JobState::RUNNING:
                                $this->status->state = $state;
                                $this->status->started = new DateTime();
                                break;
                        case JobState::SUCCESS:
                        case JobState::FINISHED:
                        case JobState::WARNING:
                        case JobState::ERROR:
                        case JobState::CRASHED:
                                $this->status->state = $state;
                                $this->status->finished = new DateTime();
                                break;
                        default:
                                $this->status->state = $state;
                }
        }

}
