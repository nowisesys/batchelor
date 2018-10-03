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

namespace Batchelor\Queue\Task;

use Batchelor\Cache\Factory;
use Batchelor\Cache\Storage;
use Batchelor\Queue\Task\Scheduler\JobQueue;
use Batchelor\System\Component;
use Batchelor\System\Service\Config;
use Batchelor\WebService\Types\JobIdentity;

/**
 * The task scheduler.
 * 
 * Web services or command line task communicates with the sceduler to push jobs
 * onto the task qeueue. The task runner consumes queued tasks from the top until
 * no more queued task exists.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Scheduler extends Component
{

        /**
         * The task queue cache,
         * @var Storage 
         */
        private $_cache;

        /**
         * Constructor.
         * @param Storage $storage The cache storage backend.
         */
        public function __construct(Storage $storage = null)
        {
                if (isset($storage)) {
                        $this->_cache = $storage;
                } else {
                        $this->_cache = $this->getCache();
                }
        }

        /**
         * Push scheduled job.
         * 
         * @param Runtime $runtime The job runtime.
         */
        public function pushJob(Runtime $runtime)
        {
                (new JobQueue($this->_cache))
                    ->addJob($runtime);
        }

        /**
         * Pop scheduled job.
         * @return Runtime
         */
        public function popJob(): Runtime
        {
                return (new JobQueue($this->_cache))
                        ->getNext();
        }

        /**
         * Check if schedule qeuue is empty.
         * @return bool
         */
        public function hasJobs(): bool
        {
                return (new JobQueue($this->_cache))
                        ->hasJobs();
        }

        /**
         * Remove queued job.
         * 
         * @param JobIdentity $job The job identity.
         * @return bool
         */
        public function removeJob(JobIdentity $job)
        {
                return (new JobQueue($this->_cache))
                        ->removeJob($job);
        }

        /**
         * Get cache backend.
         * @return Storage
         */
        private function getCache(): Storage
        {
                $options = $this->getConfig();

                if (!isset($options['options'])) {
                        $options['options'] = [];
                }
                if (!isset($options['options']['lifetime'])) {
                        $options['options']['lifetime'] = 0;
                }
                if ($options['options']['lifetime'] != 0) {
                        $options['options']['lifetime'] = 0;
                }
                if ($options['type'] == 'file') {
                        $options['options']['path'] = 'cache/schedule';
                }

                return Factory::getBackend($options['type'], $options['options']);
        }

        /**
         * Get service configuration.
         * @return array 
         */
        private function getConfig(): array
        {
                if (($config = $this->getService("app"))) {
                        if (!isset($config->cache->schedule)) {
                                return ['type' => 'detect'];
                        } elseif (is_string($config->cache->schedule)) {
                                return ['type' => $this->app->cache->schedule];
                        } else {
                                return Config::toArray($config->cache->schedule);
                        }
                }
        }

}
