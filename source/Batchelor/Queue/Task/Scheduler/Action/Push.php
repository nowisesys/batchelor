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

namespace Batchelor\Queue\Task\Scheduler\Action;

use Batchelor\Queue\Task\Runtime;
use Batchelor\Queue\Task\Scheduler;
use Batchelor\Queue\Task\Scheduler\State;
use Batchelor\WebService\Types\JobData;
use Batchelor\WebService\Types\JobIdentity;
use Batchelor\WebService\Types\JobStatus;
use Batchelor\WebService\Types\JobSubmit;
use Batchelor\WebService\Types\QueuedJob;

/**
 * Push job to scheduler.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Push
{

        /**
         * The scheduler object.
         * @var Scheduler
         */
        private $_scheduler;

        /**
         * Constructor.
         * @param Scheduler $scheduler The scheduler object.
         */
        public function __construct(Scheduler $scheduler)
        {
                $this->_scheduler = $scheduler;
        }

        public function execute(string $hostid, JobData $data): QueuedJob
        {
                $scheduler = $this->_scheduler;

                $state = new State($hostid, $data->task, $data->name);
                $runtime = $this->addRuntime($state, $data);

                $queue = $scheduler->getQueue("pending");
                $queue->addState($runtime->job, $state);

                $queue = $scheduler->getQueue($hostid);
                $queue->addState($runtime->job, $state);

                return $this->getQueuedJob($runtime, $state);
        }

        private function addRuntime(State $state, JobData $data): Runtime
        {
                $scheduler = $this->_scheduler;

                $runtime = new Runtime(self::uuid(), $data, $state->hostid, $state->result);
                $scheduler->setRuntime($runtime->job, $runtime);

                return $runtime;
        }

        private function getQueuedJob(Runtime $runtime, State $state): QueuedJob
        {
                return new QueuedJob(
                    $this->getJobIdentity($runtime), $this->getJobStatus($state), $this->getJobSubmit($state)
                );
        }

        private function getJobIdentity(Runtime $runtime): JobIdentity
        {
                return new JobIdentity(
                    $runtime->job, $runtime->result
                );
        }

        private function getJobStatus(State $state): JobStatus
        {
                return $state->status;
        }

        private function getJobSubmit(State $state): JobSubmit
        {
                return $state->submit;
        }

        private static function guidv4($data)
        {
                assert(strlen($data) == 16);

                $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
                $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

                return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        }

        private static function uuid()
        {
                if (file_exists('/proc/sys/kernel/random/uuid')) {
                        return trim(file_get_contents('/proc/sys/kernel/random/uuid'));
                } elseif (function_exists("random_bytes")) {
                        return self::guidv4(random_bytes(16));
                } elseif (function_exists("openssl_random_pseudo_bytes")) {
                        return self::guidv4(openssl_random_pseudo_bytes(16));
                } else {
                        return uniqid("", true);
                }
        }

}
