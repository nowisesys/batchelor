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

namespace Batchelor\Queue\System;

use Batchelor\Queue\Task\Scheduler;
use Batchelor\Queue\Task\Scheduler\StateQueue;
use Batchelor\Queue\WorkDirectory;
use Batchelor\Queue\WorkQueue;
use Batchelor\WebService\Types\JobData;
use Batchelor\WebService\Types\JobIdentity;
use Batchelor\WebService\Types\JobStatus;
use Batchelor\WebService\Types\QueuedJob;
use Batchelor\WebService\Types\QueueFilterResult;
use Batchelor\WebService\Types\QueueSortResult;
use RuntimeException;

/**
 * The local work queue.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class SystemQueue implements WorkQueue
{

        /**
         * {@inheritdoc}
         */
        public function addJob(string $hostid, JobData $indata): QueuedJob
        {
                return (new Scheduler())
                        ->pushJob($hostid, $indata);
        }

        /**
         * {@inheritdoc}
         */
        public function getReader(string $hostid): WorkDirectory
        {
                return new SystemDirectory($hostid);
        }

        /**
         * {@inheritdoc}
         */
        public function getStatus(string $hostid, JobIdentity $job): JobStatus
        {
                return $this->getQueue($hostid)
                        ->getState($job->jobid)->status;
        }

        /**
         * {@inheritdoc}
         */
        public function listJobs(string $hostid, QueueSortResult $sort = null, QueueFilterResult $filter = null)
        {
                $paginator = new QueuePaginator($this->getQueue($hostid));

                $paginator->setFilter($filter);
                $paginator->setSorting($sort);

                if ($filter->getValue() == QueueFilterResult::RECENT) {
                        return $paginator->getSlice(0, 3);
                } else {
                        return $paginator->getSlice();
                }
        }

        /**
         * {@inheritdoc}
         */
        public function removeJob(string $hostid, JobIdentity $job): bool
        {
                (new Scheduler())
                    ->removeJob($job->jobid);
                return true;    // Treat remove missing job as success
        }

        /**
         * {@inheritdoc}
         */
        public function resumeJob(string $hostid, JobIdentity $job): bool
        {
                // TODO: implement this method
                throw new RuntimeException("Not yet implemented");
        }

        /**
         * {@inheritdoc}
         */
        public function suspendJob(string $hostid, JobIdentity $job): bool
        {
                // TODO: implement this method
                throw new RuntimeException("Not yet implemented");
        }

        /**
         * {@inheritdoc}
         */
        public function isRemote(): bool
        {
                return false;
        }

        private function getQueue(string $hostid): StateQueue
        {
                return (new Scheduler())
                        ->getQueue($hostid);
        }

}
