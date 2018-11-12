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

namespace Batchelor\WebService\Handler;

use Batchelor\WebService\Common\ServiceBackend;
use Batchelor\WebService\Types\File;
use Batchelor\WebService\Types\JobData;
use Batchelor\WebService\Types\JobIdentity;
use Batchelor\WebService\Types\JobStatus;
use Batchelor\WebService\Types\QueuedJob;
use Batchelor\WebService\Types\QueueFilterResult;
use Batchelor\WebService\Types\QueueSortResult;
use UUP\WebService\Soap\SoapHandler;

/**
 * The SOAP service handler.
 * 
 * <code>
 * // 
 * // The default job queue is derived from connection. It's possibly to use
 * // multiple job queues and switch between them by calling select().
 * //
 * $proxy->select('my-job-queue');
 * 
 * // 
 * // Pass null to revert back to default job queue:
 * // 
 * $proxy->select(null);
 * </code>
 * 
 * <code>
 * // 
 * // Queued jobs can be listed by calling queue(). Jobs are enqueue (scheduled 
 * // for later execution) by calling enqueue:
 * // 
 * $queued = $proxy->enqueue($job);
 * 
 * // 
 * // The queued job returned contains the identity that can be used i.e. when
 * // removing a job:
 * // 
 * $proxy->dequeue($queued->identity);
 * 
 * </code>
 * 
 * <code>
 * // 
 * // The three methods opendid(), readdir() and fopen() can be used to create
 * // explorer-like interfaces. This code can be used to download all files in
 * // from a job queue:
 * // 
 * foreach ($proxy->opendir() as $identity) {
 *      foreach ($proxy->readdir($identity) as $filename) {
 *              $this->save($filename, $proxy->fopen($identity, $filename));
 *      }
 * }
 * </code>
 *
 * @since 2.0.x Clients based on 1.0 API need to update.
 * @author Anders Lövgren (Nowise Systems)
 */
class SoapServiceHandler implements SoapHandler
{

        /**
         * This method returns the list of queued jobs in current queue.
         * 
         * @param string $sort The sort options.
         * @param string $filter The filter options.
         * @return QueuedJob[]
         */
        public function queue(string $sort, string $filter)
        {
                return (new ServiceBackend())->queue(
                        new QueueSortResult($sort), new QueueFilterResult($filter)
                );
        }

        /**
         * Switch currently selected job queue.
         * 
         * Pass $queue as the name of the job queue to select it as current job
         * qeuue for next operations. Use null as argument for reverting back to
         * default queue.
         * 
         * @param string $queue The queue name.
         * @return string
         */
        public function select(string $queue)
        {
                return (new ServiceBackend())->select($queue);
        }

        /**
         * Queues an job for later execution.
         * 
         * @param JobData $indata The input data.
         * @return QueuedJob
         */
        public function enqueue(JobData $indata)
        {
                return (new ServiceBackend())->enqueue($indata);
        }

        /**
         * Dequeues an already existing job.
         * 
         * @param JobIdentity $job The job identity.
         * @return bool
         */
        public function dequeue(JobIdentity $job)
        {
                return (new ServiceBackend())->dequeue($job);
        }

        /**
         * Suspend (pause) an already running job.
         * @param JobIdentity $job The job identity.
         * @return bool
         */
        public function suspend(JobIdentity $job)
        {
                return (new ServiceBackend())->suspend($job);
        }

        /**
         * Resume (continue) an already suspended job.
         * @param JobIdentity $job The job identity.
         * @return bool
         */
        public function resume(JobIdentity $job)
        {
                return (new ServiceBackend())->resume($job);
        }

        /**
         * Get status for single job.
         * 
         * @param JobIdentity $job
         * @return JobStatus
         */
        public function stat(JobIdentity $job)
        {
                return (new ServiceBackend())->stat($job);
        }

        /**
         * Get an list of jobs enqueued after the given timestamp.
         * 
         * @param int $stamp The UNIX timestamp.
         * @return QueuedJob[]
         */
        public function watch(int $stamp)
        {
                return (new ServiceBackend())->watch($stamp);
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
                return (new ServiceBackend())->opendir();
        }

        /**
         * List files and directories in job directory.
         * 
         * Get a list of all files and directories in the job directory associated 
         * with the job identity object.
         * 
         * @param JobIdentity $job The job identity.
         * @return File[]
         */
        public function readdir(JobIdentity $job)
        {
                return (new ServiceBackend())->readdir($job);
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
                return (new ServiceBackend())->fopen($job, $file);
        }

        /**
         * Get API version.
         * @return string
         */
        public function version()
        {
                return (new ServiceBackend())->version();
        }

}
