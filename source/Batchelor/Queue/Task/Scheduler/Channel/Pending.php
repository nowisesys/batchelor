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

namespace Batchelor\Queue\Task\Scheduler\Channel;

use Batchelor\Cache\Storage;
use Batchelor\Queue\Task\Scheduler\State;
use Batchelor\WebService\Types\JobIdentity;
use Batchelor\WebService\Types\JobState;
use Batchelor\WebService\Types\JobStatus;
use InvalidArgumentException;
use LogicException;

/**
 * The pending task channel.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Pending extends State
{

        /**
         * Constructor.
         * @param Storage $cache The cache backend.
         */
        public function __construct(Storage $cache)
        {
                parent::__construct($cache, "pending");
        }

        /**
         * {@inheritdoc}
         */
        public function getName(): string
        {
                return "Pending";
        }

        /**
         * Add job to state queue.
         * 
         * The status object gets filled with date, time, timestamp, timezone 
         * and its state is set to pending.
         * 
         * @param JobIdentity $identity
         * @param JobStatus $status
         */
        public function addStatus(JobIdentity $identity, JobStatus $status)
        {
                $status->stamp = time();
                $status->date = date("Y-m-d", time());
                $status->time = date("H:i:s", time());
                $status->state = JobState::PENDING;

                $status->timezone = ini_get("date.timezone");

                parent::addStatus($identity, $status);
        }

        /**
         * Get next job.
         * 
         * Notice that the identity is incomplete and can only be used as 
         * lookup object and not as a real identity. The returned job identity
         * has an empty result field.
         * 
         * @return JobIdentity
         */
        public function getCurrent(): JobIdentity
        {
                return new JobIdentity(
                    current($this->getList()), ""
                );
        }

        /**
         * {@inheritdoc}
         */
        public function setState(JobIdentity $identity, JobState $state)
        {
                switch (($value = $state->getValue())) {
                        case JobState::PENDING:
                        case JobState::WAITING:
                                $status = $this->getStatus($identity);
                                $status->state = $state->getValue();
                                $this->setStatus($identity, $status);
                                break;
                        case JobState::RUNNING:
                                parent::setState($identity, $state);
                                break;
                        case JobState::FINISHED:
                        case JobState::SUCCESSS:
                        case JobState::WARNING:
                        case JobState::ERROR:
                        case JobState::CRASHED:
                                throw new LogicException("The transition can not be from pending to finished");
                        default:
                                throw new InvalidArgumentException("Invalid state $value encountered in finished state queue");
                }
        }

}
