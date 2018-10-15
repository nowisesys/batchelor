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
use Batchelor\Queue\Task\Scheduler\Channels;
use Batchelor\Queue\Task\Scheduler\State\Inspector;
use Batchelor\Queue\Task\Scheduler\Summary;
use Batchelor\Queue\Task\Scheduler\Tasks;
use Batchelor\System\Component;
use Batchelor\System\Service\Config;
use Batchelor\WebService\Types\JobData;
use Batchelor\WebService\Types\JobIdentity;
use Batchelor\WebService\Types\JobState;
use Batchelor\WebService\Types\JobStatus;
use Batchelor\WebService\Types\QueuedJob;

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

        public function setState(JobIdentity $identity, JobState $state)
        {
                (new Channels($this->_cache))
                    ->setState($identity, $state);
        }

        /**
         * Get inspector for pending jobs channel.
         * @return Inspector
         */
        public function getPending(): Inspector
        {
                return (new Channels($this->_cache))
                        ->getPending();
        }

        /**
         * Get inspector for running jobs channel.
         * @return Inspector
         */
        public function getRunning(): Inspector
        {
                return (new Channels($this->_cache))
                        ->getRunning();
        }

        /**
         * Get inspector for finished jobs channel.
         * @return Inspector
         */
        public function getFinished(): Inspector
        {
                return (new Channels($this->_cache))
                        ->getFinished();
        }

        /**
         * Push scheduled job.
         * 
         * @param Runtime $runtime The job runtime.
         */
        public function pushJob(Runtime $runtime)
        {
                (new Channels($this->_cache))
                    ->getPending()
                    ->addStatus(
                        $runtime->meta->identity, $runtime->meta->status
                );

                (new Tasks($this->_cache))
                    ->addTask(
                        $runtime->meta->identity, $runtime->data
                );
        }

        /**
         * Pop scheduled job.
         * @return Runtime
         */
        public function popJob(): Runtime
        {
                (new Channels($this->_cache))
                    ->usePending();
        }

        /**
         * Check if pending jobs exists.
         * @return bool
         */
        public function hasJobs(): bool
        {
                return (new Channels($this->_cache))
                        ->hasPending();
        }

        /**
         * Remove scheduled job.
         * 
         * @param JobIdentity $job The job identity.
         */
        public function removeJob(JobIdentity $identity)
        {
                (new Channels($this->_cache))
                    ->getPending()
                    ->removeStatus($identity);

                (new Tasks($this->_cache))
                    ->removeTask($identity);
        }

        /**
         * Get job details.
         * 
         * @param JobIdentity $job The job identity.
         * @return Runtime 
         */
        public function getJob(JobIdentity $identity): Runtime
        {
                return (new Channels($this->_cache))
                        ->getRuntime($identity);
        }

        /**
         * Check if job is found.
         * 
         * @param JobIdentity $identity The job identity.
         * @return bool
         */
        public function hasJob(JobIdentity $identity): bool
        {
                return (new Channels($this->_cache))
                        ->hasChannel($identity);
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
                                return [
                                        'type'    => 'detect',
                                        'options' => [
                                                'exclude' => ['apcu', 'xcache']
                                        ]
                                ];
                        } elseif (is_string($config->cache->schedule)) {
                                return ['type' => $this->app->cache->schedule];
                        } else {
                                return Config::toArray($config->cache->schedule);
                        }
                }
        }

        /**
         * Generate runtime for data.
         * 
         * @param JobData $data
         */
        public function makeRuntime(JobData $data): Runtime
        {
                return new Runtime(new QueuedJob(
                    new JobIdentity(...["", ""]), new JobStatus(...["", "", 0, JobState::NONE()])
                    ), $data);
        }

        /**
         * Get scheduler summary.
         * @return Summary
         */
        public function getSummary(): Summary
        {
                return new Summary($this);
        }

}
