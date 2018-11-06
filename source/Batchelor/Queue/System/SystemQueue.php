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
        public function listJobs(string $hostid, QueueSortResult $sort = null, QueueFilterResult $filter = null, array $queued = [])
        {
                // 
                // Include all jobs and don't sort by default:
                // 
                if (!isset($sort)) {
                        $sort = QueueSortResult::NONE();
                }
                if (!isset($filter)) {
                        $filter = QueueFilterResult::NONE();
                }

                // 
                // Filter jobs on job state:
                // 
                foreach ($this->getQueue($hostid) as $jobid => $state) {
                        if ($filter->getValue() == QueueFilterResult::NONE) {
                                $queued[] = $state->getQueuedJob($jobid);
                        } elseif ($filter->getValue() == $state->status->state->getValue()) {
                                $queued[] = $state->getQueuedJob($jobid);
                        }
                }

                // 
                // Sort result array on request:
                // 
                switch ($sort->getValue()) {
                        case QueueSortResult::JOBID:
                                usort($queued, static function($a, $b) {
                                        return strcmp($a->identity->jobid, $b->identity->jobid);
                                });
                                break;
                        case QueueSortResult::NAME:
                                usort($queued, static function($a, $b) {
                                        return strcmp($a->submit->name, $b->submit->name);
                                });
                                break;
                        case QueueSortResult::PUBLISHED:
                                // TODO: Do we need to support published?
                                throw new RuntimeException("Not yet implemented");
                                break;
                        case QueueSortResult::STARTED:
                                usort($queued, static function($a, $b) {
                                        return strcmp($a->identity->jobid, $b->identity->jobid);
                                });
                                break;
                        case QueueSortResult::STATE:
                                usort($queued, static function($a, $b) {
                                        return strcmp($a->status->state->getValue(), $b->status->state->getValue());
                                });
                                break;
                        case QueueSortResult::TASK:
                                usort($queued, static function($a, $b) {
                                        return strcmp($a->submit->task, $b->submit->task);
                                });
                                break;
                }

                return $queued;
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
