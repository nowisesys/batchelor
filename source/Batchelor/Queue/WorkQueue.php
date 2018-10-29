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

namespace Batchelor\Queue;

use Batchelor\WebService\Types\JobData;
use Batchelor\WebService\Types\JobIdentity;
use Batchelor\WebService\Types\JobStatus;
use Batchelor\WebService\Types\QueuedJob;
use Batchelor\WebService\Types\QueueFilterResult;
use Batchelor\WebService\Types\QueueSortResult;

/**
 * Interface for work queues.
 * 
 * Work queues are the frontend against web services and the source of information
 * about batch jobs (scheduled, running or finished). The work queue is an opaque 
 * object seen from the web service view that can have a concrete implementation as
 * a local or remote queue.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
interface WorkQueue
{

        /**
         * Add job in queue.
         * 
         * @param string $hostid The host ID.
         * @param JobData $indata The job data.
         * @return QueuedJob
         */
        function addJob(string $hostid, JobData $indata): QueuedJob;

        /**
         * Removes the job from queue.
         * 
         * This method will delete the job and its data completely. If the job
         * is currently scheduled, then it should be removed from scheduler as 
         * part of this task.
         * 
         * @param string $hostid The host ID.
         * @param JobIdentity $job The job identity object.
         * @return bool
         */
        function removeJob(string $hostid, JobIdentity $job): bool;

        /**
         * Suspend (pause) an running job.
         * 
         * If the job is not running, then this is an noop that should return
         * true. Only return false if suspending the job fails.
         * 
         * @param string $hostid The host ID.
         * @param JobIdentity $job The job identity object.
         * @return bool
         */
        function suspendJob(string $hostid, JobIdentity $job): bool;

        /**
         * Resume (continue) an running job.
         * 
         * If the job is not paused, then this is an noop that should return
         * true. Only return false if resuming the job fails.
         * 
         * @param string $hostid The host ID.
         * @param JobIdentity $job The job identity object.
         * @return bool
         */
        function resumeJob(string $hostid, JobIdentity $job): bool;

        /**
         * Get all jobs matching filter.
         * 
         * @param string $hostid The host ID.
         * @param QueueSortResult $sort The sort options.
         * @param QueueFilterResult $filter The filter options.
         * @return QueuedJob[] 
         */
        function listJobs(string $hostid, QueueSortResult $sort = QueueSortResult ::STARTED, QueueFilterResult $filter = QueueFilterResult::NONE);

        /**
         * Get reader for job queue.
         * 
         * @param string $hostid The host ID.
         * @return WorkDirectory The work directory reader.
         */
        function getReader(string $hostid): WorkDirectory;

        /**
         * Get job status.
         * 
         * @param string $hostid The host ID.
         * @param JobIdentity $job The job identity object.
         * @return JobStatus The status object.
         */
        function getStatus(string $hostid, JobIdentity $job): JobStatus;

        /**
         * Check if queue is remote or local.
         * @return bool
         */
        function isRemote(): bool;
}
