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

namespace Batchelor\Queue\Remote;

use Batchelor\Queue\WorkDirectory;
use Batchelor\Queue\WorkQueue;
use Batchelor\WebService\Types\JobData;
use Batchelor\WebService\Types\JobIdentity;
use Batchelor\WebService\Types\JobStatus;
use Batchelor\WebService\Types\QueueFilterResult;
use Batchelor\WebService\Types\QueueSortResult;

/**
 * The remote work queue.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class RemoteQueue implements WorkQueue
{

        /**
         * {@inheritdoc}
         */
        public function addJob(string $hostid, JobData $indata)
        {
                // TODO: implement using JSON API
        }

        /**
         * {@inheritdoc}
         */
        public function getReader(string $hostid): WorkDirectory
        {
                // TODO: implement using JSON API
        }

        /**
         * {@inheritdoc}
         */
        public function getStatus(string $hostid, JobIdentity $job): JobStatus
        {
                // TODO: implement using JSON API
        }

        /**
         * {@inheritdoc}
         */
        public function listJobs(string $hostid, QueueSortResult $sort = QueueSortResult::STARTED, QueueFilterResult $filter = QueueFilterResult::NONE)
        {
                // TODO: implement using JSON API
        }

        /**
         * {@inheritdoc}
         */
        public function removeJob(string $hostid, JobIdentity $job): bool
        {
                // TODO: implement using JSON API
        }

        /**
         * {@inheritdoc}
         */
        public function resumeJob(string $hostid, JobIdentity $job): bool
        {
                // TODO: implement using JSON API
        }

        /**
         * {@inheritdoc}
         */
        public function suspendJob(string $hostid, JobIdentity $job): bool
        {
                // TODO: implement using JSON API
        }

        /**
         * {@inheritdoc}
         */
        public function isRemote(): bool
        {
                return true;
        }

}
