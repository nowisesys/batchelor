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
use Batchelor\Queue\Task\Scheduler\Action\Add as AddAction;
use Batchelor\Queue\Task\Scheduler\Action\Pop as PopAction;
use Batchelor\Queue\Task\Scheduler\Action\Push as PushAction;
use Batchelor\Queue\Task\Scheduler\Action\Remove as RemoveAction;
use Batchelor\Queue\Task\Scheduler\Action\Transition;
use Batchelor\Queue\Task\Scheduler\Inspector;
use Batchelor\Queue\Task\Scheduler\State;
use Batchelor\Queue\Task\Scheduler\StateQueue;
use Batchelor\Queue\Task\Scheduler\Summary;
use Batchelor\System\Component;
use Batchelor\System\Service\Config;
use Batchelor\WebService\Types\JobData;
use Batchelor\WebService\Types\JobState;
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

        /**
         * Push job action.
         * 
         * Insert job state in pending and hostid queue. The job data is used for
         * creating the runtime object. When pop job is called for the inserted
         * job, the runtime will contains supplied job data.
         * 
         * Returns a queue job object containing the job identity. This object 
         * can be used by web services for future interaction with the job queue, 
         * processor or scheduler.
         * 
         * @param string $hostid The host ID.
         * @param JobData $data The job data.
         * @return QueuedJob
         */
        public function pushJob(string $hostid, JobData $data): QueuedJob
        {
                return (new PushAction($this))->execute($hostid, $data);
        }

        /**
         * Pop job action.
         * 
         * Removes first job in the pending queue and transition it to running
         * queue. Returns the runtime data.
         * 
         * @return Runtime
         */
        public function popJob(): Runtime
        {
                return (new PopAction($this))->execute();
        }

        /**
         * Check if pending jobs exists.
         * @return bool
         */
        public function hasJobs(): bool
        {
                $queue = $this->getQueue("pending");
                return $queue->isEmpty() == false;
        }

        /**
         * Check if job exists.
         * 
         * @param string $job The job ID.
         * @return bool
         */
        public function hasJob(string $job): bool
        {
                $ckey = sprintf("scheduler-%s-runtime", $job);
                return $this->_cache->exists($ckey);
        }

        /**
         * Add job action.
         * 
         * This method is for appending a child job for an existing job. This 
         * method can be used to implement pipelines and splitted jobs if indata
         * is large.
         * 
         * @param string $job The job ID.
         * @param JobData $data The job data.
         */
        public function addJob(string $job, JobData $data)
        {
                (new AddAction($this))->execute($job, $data);
        }

        /**
         * Remove job action.
         * 
         * The job is removed from the intrinsic queues (i.e. finished) and 
         * from the hostid queue. Calling this method will also delete runtime 
         * data. 
         * 
         * It's the callers responsibility to remove working directory and other 
         * files associated with the job.
         * 
         * @param string $job The job ID.
         */
        public function removeJob(string $job)
        {
                (new RemoveAction($this))->execute($job);

                $ckey = sprintf("scheduler-%s-runtime", $job);
                $this->_cache->delete($ckey);
        }

        /**
         * Get job queue.
         * 
         * The ident parameter is the name of one of the intrisic queue (i.e. 
         * running) or an hostid queue. The hostid queues are created on the 
         * fly by peer users.
         * 
         * @param string $ident The job queue identity.
         * @return StateQueue
         */
        public function getQueue(string $ident): StateQueue
        {
                return new StateQueue($ident, $this->_cache);
        }

        /**
         * Get runtime for job.
         * 
         * @param string $job The job ID.
         * @return Runtime
         */
        public function getRuntime(string $job): Runtime
        {
                $ckey = sprintf("scheduler-%s-runtime", $job);
                return $this->_cache->read($ckey);
        }

        /**
         * Set runtime for job.
         * 
         * @param string $job The job ID.
         * @param Runtime $runtime The runtime object.
         */
        public function setRuntime(string $job, Runtime $runtime)
        {
                $ckey = sprintf("scheduler-%s-runtime", $job);
                $this->_cache->save($ckey, $runtime);
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
         * Get scheduler summary.
         * @return Summary
         */
        public function getSummary(): Summary
        {
                return new Summary($this);
        }

        /**
         * Get inspector for pending queue.
         * @return Inspector
         */
        public function getPending(): Inspector
        {
                return $this->getQueue("pending");
        }

        /**
         * Get inspector for riunning queue.
         * @return Inspector
         */
        public function getRunning(): Inspector
        {
                return $this->getQueue("running");
        }

        /**
         * Get inspector for finished queue.
         * @return Inspector
         */
        public function getFinished(): Inspector
        {
                return $this->getQueue("finished");
        }

        /**
         * Set suspended status on job.
         * 
         * Transition job form running queue to suspend. The task processor should
         * invoke this method in response to child process being stopped.
         * 
         * @param string $job The job ID.
         */
        public function setSuspend(string $job)
        {
                (new Transition($this))
                    ->execute($job, "running", "suspend", static function(State &$state) {
                            $state->setState(JobState::SUSPEND());
                    });
        }

        /**
         * Set resumed status on job.
         * 
         * Transition job from suspended jb queue to resumed. The task processor 
         * should poll this queue reqular to pick up resumed jobs.
         * 
         * @param string $job The job ID.
         */
        public function setResume(string $job)
        {
                (new Transition($this))
                    ->execute($job, "suspend", "resumed", static function(State &$state) {
                            $state->setState(JobState::RESUMED());
                    });
        }

        /**
         * Set pending status on job.
         * 
         * Transitions the job from finished queue to pending. This method is for
         * restarting an job, for example if failed because input date could not
         * be downloaded.
         * 
         * @param string $job The job ID.
         */
        public function setPending(string $job)
        {
                (new Transition($this))
                    ->execute($job, "finished", "pending", static function(State &$state) {
                            $state->setState(JobState::PENDING());
                    });
        }

        /**
         * Set running status on job.
         * 
         * Transitions the job from pending queue to running. Sets the start time
         * on job.
         * 
         * @param string $job The job ID.
         */
        public function setRunning(string $job)
        {
                (new Transition($this))
                    ->execute($job, "pending", "running", static function(State &$state) {
                            $state->setState(JobState::RUNNING());
                    });
        }

        /**
         * Set finished status on job.
         * 
         * Transitions the job from running queue to finished. The status is the 
         * final state for this job (i.e. success or error).
         * 
         * @param string $job The job ID.
         * @param JobState $status The job status.
         */
        public function setFinished(string $job, JobState $status)
        {
                (new Transition($this))
                    ->execute($job, "running", "finished", static function(State &$state) use($status) {
                            $state->setState($status);
                    });
        }

}
