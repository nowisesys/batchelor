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

namespace Batchelor\Queue\Task\Scheduler\State;

use Batchelor\WebService\Types\JobIdentity;
use Batchelor\WebService\Types\JobStatus;

/**
 * The state queue mutator.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
interface Mutator
{

        /**
         * Add job to state queue.
         * 
         * @param JobIdentity $identity The job identity.
         * @param JobStatus $status The job status.
         */
        function addStatus(JobIdentity $identity, JobStatus $status);

        /**
         * Remove job from state queue.
         * @param JobIdentity $identity The job identity.
         */
        function removeStatus(JobIdentity $identity);

        /**
         * Get job status.
         * 
         * @param JobIdentity $identity The job identity.
         * @return JobStatus
         */
        function getStatus(JobIdentity $identity): JobStatus;

        /**
         * Set job status.
         * 
         * @param JobIdentity $identity The job identity.
         * @param JobStatus $status The job status.
         */
        function setStatus(JobIdentity $identity, JobStatus $status);
}
