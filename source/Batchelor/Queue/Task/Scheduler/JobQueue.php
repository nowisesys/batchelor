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

namespace Batchelor\Queue\Task\Scheduler;

use Batchelor\Cache\Storage;
use Batchelor\Queue\Task\Runtime;
use Batchelor\WebService\Types\JobIdentity;

/**
 * The task queue.
 *
 * Simple job queue (FIFO) used by the scheduler. You're not support to use this 
 * class directly.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class JobQueue
{

        /**
         * The cache backend.
         * @var Storage 
         */
        private $_cache;

        /**
         * Constructor.
         * @param Storage $cache The cache storage backend.
         */
        public function __construct(Storage $cache)
        {
                $this->_cache = $cache;
        }

        /**
         * Check if qeueu is empty.
         * @return bool
         */
        public function hasJobs(): bool
        {
                return $this->getCount() > 0;
        }

        /**
         * Add job to queue.
         * @param Runtime $runtime The job runtime.
         */
        public function addJob(Runtime $runtime)
        {
                $this->addData($runtime, $this->addNext());
        }

        /**
         * Save runtime data.
         * 
         * @param Runtime $runtime The job runtime.
         * @param string $name The cache key.
         */
        private function addData(Runtime $runtime, string $name)
        {
                $this->_cache->save($name, $runtime);
        }

        /**
         * Get next job from queue.
         * 
         * Calling this method will consume next job from queue and decrement
         * the queue count. Caller should check first if job queue is non-empty
         * before calling this method.
         * 
         * @return Runtime The job runtime.
         */
        public function getNext(): Runtime
        {
                $count = $this->getCount();
                $queue = $this->getQueue();

                $name = array_shift($queue);

                $this->_cache->save("schedule-count", $count - 1);
                $this->_cache->save("schedule-queue", $queue);

                return $this->_cache->read($name);
        }

        /**
         * Remove queued job.
         * 
         * @param JobIdentity $job The job identity.
         * @return bool
         */
        public function removeJob(JobIdentity $job): bool
        {
                $count = $this->getCount();
                $queue = $this->getQueue();

                $name = sprintf("schedule-task-%d", $job->jobid);

                if (($key = array_search($name, $queue)) !== false) {
                        unset($queue[$key]);
                        $this->_cache->save("schedule-count", $count - 1);
                }

                return $key !== false;
        }

        /**
         * Add next job.
         * @return string Key for added task.
         */
        private function addNext(): string
        {
                $index = $this->getIndex();
                $count = $this->getCount();
                $queue = $this->getQueue();

                $name = sprintf("schedule-task-%d", $index + 1);
                $queue[] = $name;

                $this->_cache->save("schedule-index", $index + 1);
                $this->_cache->save("schedule-count", $count + 1);
                $this->_cache->save("schedule-queue", $queue);

                return $name;
        }

        /**
         * Get task count.
         * @return int
         */
        private function getCount(): int
        {
                if ($this->_cache->exists("schedule-count")) {
                        return $this->_cache->read("schedule-count");
                } else {
                        return 0;
                }
        }

        /**
         * Get task index.
         * @return int
         */
        private function getIndex(): int
        {
                if ($this->_cache->exists("schedule-index")) {
                        return $this->_cache->read("schedule-index");
                } else {
                        return 0;
                }
        }

        /**
         * Get queued job names.
         * @return array 
         */
        private function getQueue(): array
        {
                if ($this->_cache->exists("schedule-queue")) {
                        return $this->_cache->read("schedule-queue");
                } else {
                        return [];
                }
        }

}
