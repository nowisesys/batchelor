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

namespace Batchelor\WebService\Common;

use Batchelor\System\Component;
use Batchelor\WebService\Types\JobIdentity;
use Batchelor\WebService\Types\JobStatus;
use Batchelor\WebService\Types\QueuedJob;
use Batchelor\WebService\Types\QueueFilterResult;
use Batchelor\WebService\Types\QueueSortResult;

/**
 * Common web service backend.
 * 
 * All web service frontends should use this class for interaction with
 * the batch queue. This class function as an common API.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class ServiceBackend extends Component
{

        /**
         * The API version.
         */
        const API_VERSION = "2.0";

        public function __construct()
        {
                
        }

        /**
         * Dequeues an already existing job.
         * 
         * @param JobIdentity $job The job identity.
         * @return bool
         */
        public function dequeue(JobIdentity $job)
        {
                
        }

        /**
         * Queues an job for later execution.
         * 
         * @param string $indata The input data.
         * @return QueuedJob[]
         */
        public function enqueue(string $indata)
        {
                
        }

        /**
         * Read file content.
         * 
         * Opens the given file from the job directory associated with the job 
         * identity object and return its content.
         * 
         * @param JobIdentity $job The job identity.
         * @param string $file The filename.
         * @return string
         */
        public function fopen(JobIdentity $job, string $file)
        {
                
        }

        /**
         * List all queued jobs.
         * 
         * Call this method to return a list of all jobs in the current selected 
         * batch queue.
         * 
         * @return JobIdentity[]
         */
        public function opendir()
        {
                
        }

        /**
         * This method returns the list of queued jobs.
         * 
         * @param QueueSortResult $sort The sort options.
         * @param QueueFilterResult $filter The filter options.
         * @return QueuedJob[]
         */
        public function queue(QueueSortResult $sort, QueueFilterResult $filter)
        {
                
        }

        /**
         * Get a list of all files and directories in the job directory associated 
         * with the job identity object.
         * 
         * @param JobIdentity $job The job identity.
         * @return string[]
         */
        public function readdir(JobIdentity $job)
        {
                
        }

        /**
         * Suspend (pause) an already running job.
         * @param JobIdentity $job The job identity.
         * @return bool
         */
        public function suspend(JobIdentity $job)
        {
                
        }

        /**
         * Resume (continue) an already suspended job.
         * @param JobIdentity $job The job identity.
         * @return bool
         */
        public function resume(JobIdentity $job)
        {
                
        }

        /**
         * Get status for single job.
         * 
         * @param JobIdentity $job
         * @return JobStatus
         */
        public function stat(JobIdentity $job)
        {
                
        }

        /**
         * Get an list of jobs enqueued after the given timestamp,
         * 
         * @param int $stamp The UNIX timestamp.
         * @return QueuedJob[]
         */
        public function watch(int $stamp)
        {
                
        }

        /**
         * Switch selected job queue.
         * 
         * Pass name of the queue to select as current queue for comming operations. 
         * Revert back to default queue by passing null. The returned string is the
         * hostid for the activated queue.
         * 
         * @param string $queue The queue name.
         * @return string The new hostid
         */
        public function select($queue)
        {
                $this->hostid->setQueue($queue);
                return $this->hostid->getValue();
        }

        /**
         * Get API version.
         * @return string
         */
        public function version()
        {
                return self::API_VERSION;
        }

}
