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

namespace Batchelor\WebService\Types;

use DateTime;
use InvalidArgumentException;

/**
 * The job status class.
 * 
 * Represent the runtime status of an queued job. Contains the current execution 
 * state (i.e. pending, finished or failed) and the datetime of enquing. The stamp
 * is the UNIX timestamp for enqeuing. Use the timezone member for localizining 
 * time values.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class JobStatus
{

        /**
         * When job was queued.
         * @var DateTime 
         */
        public $queued;
        /**
         * When job was started.
         * @var DateTime 
         */
        public $started;
        /**
         * When job was finished.
         * @var DateTime 
         */
        public $finished;
        /**
         * The job state.
         * @var JobState 
         */
        public $state;

        /**
         * Constructor.
         * 
         * @param DateTime $queued The enqueue date.
         * @param JobState $state The job state.
         */
        public function __construct(DateTime $queued, JobState $state)
        {
                if (!isset($queued->date)) {
                        json_encode($queued);   // Ugly cludge for serialization
                }

                $this->queued = $queued;
                $this->state = $state;
        }

        /**
         * Create job status object.
         * 
         * @param array $data The job status input.
         * @return JobStatus
         * @throws InvalidArgumentException
         */
        public static function create(array $data): self
        {
                if(!isset($data['queued'])) {
                        throw new InvalidArgumentException("The queued key is missing in job status");                        
                }
                if(!isset($data['state'])) {
                        throw new InvalidArgumentException("The state key is missing in job status");                        
                }
                return new self(
                    new DateTime($data['queued']['date']), new JobState($data['state'])
                );
        }

}
