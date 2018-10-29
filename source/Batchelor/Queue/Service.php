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

use Batchelor\System\Component;
use Batchelor\System\Service\Config;
use Batchelor\WebService\Types\JobData;
use Batchelor\WebService\Types\JobIdentity;
use Batchelor\WebService\Types\JobStatus;
use Batchelor\WebService\Types\QueuedJob;
use Batchelor\WebService\Types\QueueFilterResult;
use Batchelor\WebService\Types\QueueSortResult;

/**
 * The queue service.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Service extends Component implements WorkQueue
{

        /**
         * The queue locator.
         * @var Locator 
         */
        private $_locator;

        /**
         * Constructor.
         * @param array $options The locator options.
         */
        public function __construct(array $options = [])
        {
                if (empty($options)) {
                        $options = $this->getConfig();
                }
                if (isset($options)) {
                        $this->_locator = new Locator($options);
                }
        }

        /**
         * {@inheritdoc}
         */
        public function addJob(string $hostid, JobData $indata): QueuedJob
        {
                return $this->getQueue($hostid)
                        ->addJob($hostid, $indata);
        }

        /**
         * {@inheritdoc}
         */
        public function getReader(string $hostid): WorkDirectory
        {
                return $this->getQueue($hostid)
                        ->getReader($hostid);
        }

        /**
         * {@inheritdoc}
         */
        public function getStatus(string $hostid, JobIdentity $job): JobStatus
        {
                return $this->getQueue($hostid)
                        ->getStatus($hostid, $job);
        }

        /**
         * {@inheritdoc}
         */
        public function listJobs(string $hostid, QueueSortResult $sort = QueueSortResult::STARTED, QueueFilterResult $filter = QueueFilterResult::NONE)
        {
                return $this->getQueue($hostid)
                        ->listJobs($hostid, $sort, $filter);
        }

        /**
         * {@inheritdoc}
         */
        public function removeJob(string $hostid, JobIdentity $job): bool
        {
                return $this->getQueue($hostid)
                        ->removeJob($hostid, $job);
        }

        /**
         * {@inheritdoc}
         */
        public function resumeJob(string $hostid, JobIdentity $job): bool
        {
                return $this->getQueue($hostid)
                        ->resumeJob($hostid, $job);
        }

        /**
         * {@inheritdoc}
         */
        public function suspendJob(string $hostid, JobIdentity $job): bool
        {
                return $this->getQueue($hostid)
                        ->suspendJob($hostid, $job);
        }

        /**
         * {@inheritdoc}
         */
        public function isRemote(): bool
        {
                if (($hostid = $this->getService("hostid"))) {
                        return $this->getQueue($hostid->getValue())
                                ->isRemote();
                }
        }

        /**
         * Get work queue.
         * 
         * @param string $hostid The hostid string.
         * @return WorkQueue 
         */
        private function getQueue(string $hostid): WorkQueue
        {
                return $this->_locator->useQueue($hostid);
        }

        /**
         * Get service configuration.
         * @return array 
         */
        private function getConfig(): array
        {
                if (($config = $this->getService("app"))) {
                        if (!isset($config->queue)) {
                                return [];
                        } elseif (is_string($config->queue)) {
                                return [];
                        } elseif (is_bool($config->queue) && $config->queue) {
                                return [];
                        } else {
                                return Config::toArray($config->queue);
                        }
                }
        }

}
