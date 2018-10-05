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
use InvalidArgumentException;
use LogicException;

/**
 * The running task channel.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Running extends State
{

        /**
         * Constructor.
         * @param Storage $cache The cache backend.
         */
        public function __construct(Storage $cache)
        {
                parent::__construct($cache, "running");
        }

        /**
         * {@inheritdoc}
         */
        public function getName(): string
        {
                return "Running";
        }

        /**
         * {@inheritdoc}
         */
        public function setState(JobIdentity $identity, JobState $state)
        {
                switch (($value = $state->getValue())) {
                        case JobState::PENDING:
                        case JobState::WAITING:
                                throw new LogicException("The transition can not be from running to pending");
                        case JobState::RUNNING:
                                break;
                        case JobState::FINISHED:
                        case JobState::SUCCESSS:
                        case JobState::WARNING:
                        case JobState::ERROR:
                        case JobState::CRASHED:
                                parent::setState($identity, $state);
                        default:
                                throw new InvalidArgumentException("Invalid state $value encountered in finished state queue");
                }
        }

}
