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
 * The state queue inspector.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
interface Inspector
{

        /**
         * The number of items in queue.
         * @return int 
         */
        function count(): int;

        /**
         * The last index used.
         * @return int 
         */
        function index(): int;

        /**
         * Get queue data.
         * @return array 
         */
        function queue(): array;

        /**
         * Check if status object exists.
         * 
         * @param JobIdentity $identity
         * @return bool True if status object exist in this state queue.
         */
        function hasStatus(JobIdentity $identity): bool;

        /**
         * Get job status.
         * 
         * @param JobIdentity $identity The job identity.
         * @return JobStatus 
         */
        function getStatus(JobIdentity $identity): JobStatus;

        /**
         * Get queued jobs.
         * 
         * Returns a list of queued jobs. The values are the job ID.
         * 
         * @return array
         */
        function getList(): array;

        /**
         * Check if queue is empty.
         * @return bool
         */
        function isEmpty(): bool;

        /**
         * Get queue name.
         * @return string 
         */
        function getName(): string;
}
