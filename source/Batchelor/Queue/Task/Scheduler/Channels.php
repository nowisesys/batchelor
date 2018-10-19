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

use Batchelor\Cache\Storage;
use Batchelor\Queue\Task\Runtime;
use Batchelor\Queue\Task\Scheduler\Channel\Finished;
use Batchelor\Queue\Task\Scheduler\Channel\Pending;
use Batchelor\Queue\Task\Scheduler\Channel\Running;
use Batchelor\WebService\Types\JobIdentity;
use Batchelor\WebService\Types\JobState;
use Batchelor\WebService\Types\QueuedJob;
use InvalidArgumentException;

/**
 * Schedule queue channels.
 * 
 * Each channel in the scheduler maintains a queue of job states. This class 
 * aggregates all channels in a single class and provides the needed iinterface 
 * for scheduler to migrate jobs between channels.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Channels
{

        /**
         * The cache backend.
         * @var Storage 
         */
        private $_cache;

        /**
         * Constructor.
         * @param Storage $cache The cache backend.
         */
        public function __construct(Storage $cache)
        {
                $this->_cache = $cache;
        }

        /**
         * Check if pending job exists.
         * @return bool
         */
        public function hasPending(): bool
        {
                return (new Pending($this->_cache))
                        ->isEmpty() == false;
        }

        /**
         * Check if running job exists.
         * @return bool
         */
        public function hasRunning(): bool
        {
                return (new Running($this->_cache))
                        ->isEmpty() == false;
        }

        /**
         * Check if finished job exists.
         * @return bool
         */
        public function hasFinished(): bool
        {
                return (new Finished($this->_cache))
                        ->isEmpty() == false;
        }

        /**
         * Use pending jobs channel.
         * @return State
         */
        public function usePending(): Pending
        {
                return new Pending($this->_cache);
        }

        /**
         * Use running jobs channel.
         * @return State
         */
        public function useRunning(): Running
        {
                return new Running($this->_cache);
        }

        /**
         * Use finished jobs channel.
         * @return State
         */
        public function useFinished(): Finished
        {
                return new Finished($this->_cache);
        }

        /**
         * Set job state.
         * 
         * Calling this method might transition to job to another state queue
         * if the state don't belong in current state queue.
         * 
         * @param JobIdentity $identity The job identity.
         * @param JobState $state The job state.
         * @throws InvalidArgumentException
         */
        public function setState(JobIdentity $identity, JobState $state)
        {
                switch ($state->getValue()) {
                        case JobState::PENDING:
                        case JobState::WAITING:
                                $this->setPending($identity, $state);
                                break;
                        case JobState::RUNNING:
                                $this->setRunning($identity, $state);
                                break;
                        case JobState::FINISHED:
                        case JobState::SUCCESSS:
                        case JobState::WARNING:
                        case JobState::ERROR:
                        case JobState::CRASHED:
                                $this->setFinished($identity, $state);
                                break;
                }
        }

        /**
         * Set pending state.
         * 
         * @param JobIdentity $identity The job identity.
         * @param JobState $state The job state.
         */
        private function setPending(JobIdentity $identity, JobState $state)
        {
                if (!$this->usePending()->hasStatus($identity)) {
                        $this->useFinished()->setState($identity, $state);
                } else {
                        $this->usePending()->setState($identity, $state);
                }
        }

        /**
         * Set running state.
         * 
         * @param JobIdentity $identity The job identity.
         * @param JobState $state The job state.
         */
        private function setRunning(JobIdentity $identity, JobState $state)
        {
                if (!$this->useRunning()->hasStatus($identity)) {
                        $this->usePending()->setState($identity, $state);
                } else {
                        $this->useRunning()->setState($identity, $state);
                }
        }

        /**
         * Set finished state.
         * 
         * @param JobIdentity $identity The job identity.
         * @param JobState $state The job state.
         */
        private function setFinished(JobIdentity $identity, JobState $state)
        {
                if (!$this->useFinished()->hasStatus($identity)) {
                        $this->useRunning()->setState($identity, $state);
                } else {
                        $this->useFinished()->setState($identity, $state);
                }
        }

        /**
         * Get channel holding identity.
         * 
         * This method is a bit inefficient and should be used rarely. Fortunate,
         * this method is only needed for looking up job data from scheduler, a
         * task that is never really needed in normal cases.
         * 
         * @param JobIdentity $identity
         * @return State The state queue.
         */
        private function getChannel(JobIdentity $identity): State
        {
                if (($channel = $this->usePending()) && $channel->hasStatus($identity)) {
                        return $channel;
                }
                if (($channel = $this->useRunning()) && $channel->hasStatus($identity)) {
                        return $channel;
                }
                if (($channel = $this->useFinished()) && $channel->hasStatus($identity)) {
                        return $channel;
                }
        }

        /**
         * Check if any channel contains job identity.
         * 
         * This method is a bit inefficient bacause it will potential check all 
         * channels to see it job identity is present. Should be used non-frequent,
         * which is the normal case.
         * 
         * @param JobIdentity $identity The job identity
         * @return bool
         */
        public function hasChannel(JobIdentity $identity): bool
        {
                if (($channel = $this->usePending()) && $channel->hasStatus($identity)) {
                        return true;
                }
                if (($channel = $this->useRunning()) && $channel->hasStatus($identity)) {
                        return true;
                }
                if (($channel = $this->useFinished()) && $channel->hasStatus($identity)) {
                        return true;
                }

                return false;
        }

        /**
         * Get runtime for identity.
         * 
         * Don't call this method unless required. It does a scan on all state
         * queues to find the channel containing the job identity. Not that bad,
         * but if a state queue is already known to hold the identity, then call
         * is own method to get runtime direct.
         * 
         * @param JobIdentity $identity The job identity.
         * @return Runtime
         */
        public function getRuntime(JobIdentity $identity): Runtime
        {
                return $this->getChannel($identity)
                        ->getRuntime($identity);
        }

        /**
         * Get next pending job.
         * @return Runtime 
         */
        public function getPending(): Runtime
        {
                $pending = $this->usePending();
                $current = $pending->getCurrent();
                $runtime = $pending->getRuntime($current);

                $pending->setState($current, JobState::RUNNING()); // Migrate job to running

                return $runtime;
        }

        /**
         * Remove job identity.
         * 
         * @param JobIdentity $identity The job identity.
         */
        public function removeIdentity(JobIdentity $identity)
        {
                if (!$this->hasChannel($identity)) {
                        throw new LogicException("The job is missing");
                }

                $this->getChannel($identity)
                    ->removeStatus($identity);
        }

}
