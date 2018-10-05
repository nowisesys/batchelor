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
use Batchelor\WebService\Types\JobData;
use Batchelor\WebService\Types\JobIdentity;

/**
 * The task lookup class.
 * 
 * This class provides storage and lookup of runtime data for a job based on
 * job identity. Listing of stored data is not supported, instead the caller has 
 * to know requested job. The existance of cached data can be checked though.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Tasks
{

        /**
         * The cache backend.
         * @var Storage 
         */
        private $_cache;

        /**
         * Constructor.
         * @param Storage $cache The cache backend.
         */
        public function __construct(Storage $cache)
        {
                $this->_cache = $cache;
        }

        /**
         * Add task data.
         * 
         * @param JobIdentity $identity The job identity.
         * @param JobData $data The job data.
         */
        public function addTask(JobIdentity $identity, JobData $data)
        {
                $this->setCache(new Task(
                    $identity, $data
                ));
        }

        /**
         * Get task data.
         * 
         * @param JobIdentity $identity The job identity.
         * @return Task
         */
        public function getTask(JobIdentity $identity): Task
        {
                return $this->getCache($identity);
        }

        /**
         * Check if task data exists.
         * 
         * @param JobIdentity $identity
         * @return bool 
         */
        public function hasTask(JobIdentity $identity): bool
        {
                return $this->hasCache($identity);
        }

        /**
         * Remove task data.
         * 
         * @param JobIdentity $identity The job identity.
         */
        public function removeTask(JobIdentity $identity)
        {
                $cache = $this->_cache;
                $cname = sprintf("schedule-task-%s", $identity->jobid);

                $cache->delete($cname);
        }

        /**
         * Check if cache key exists.
         * 
         * @param JobIdentity $identity The job identity.
         * @return bool
         */
        private function hasCache(JobIdentity $identity): bool
        {
                $cache = $this->_cache;
                $cname = sprintf("schedule-task-%s", $identity->jobid);

                return $cache->exists($cname);
        }

        /**
         * Get cache data.
         * 
         * @param JobIdentity $identity The job identity.
         * @return \Batchelor\Queue\Task\Scheduler\Task
         */
        private function getCache(JobIdentity $identity): Task
        {
                $cache = $this->_cache;
                $cname = sprintf("schedule-task-%s", $identity->jobid);

                if ($cache->exists($cname)) {
                        return $cache->read($cname);
                }
        }

        /**
         * Set cache data.
         * 
         * @param \Batchelor\Queue\Task\Scheduler\Task $task The task.
         */
        private function setCache(Task $task)
        {
                $cache = $this->_cache;
                $cname = sprintf("schedule-task-%s", $task->identity->jobid);

                $cache->save($cname, $task);
        }

}
