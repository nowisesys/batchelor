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

use ArrayIterator;
use Batchelor\Cache\Storage;
use Batchelor\Queue\Task\Runtime;
use Batchelor\Queue\Task\Scheduler\Channel\Finished;
use Batchelor\Queue\Task\Scheduler\Channel\Pending;
use Batchelor\Queue\Task\Scheduler\Channel\Running;
use Batchelor\Queue\Task\Scheduler\State\Inspector;
use Batchelor\Queue\Task\Scheduler\State\Migration;
use Batchelor\Queue\Task\Scheduler\State\Mutator;
use Batchelor\WebService\Types\JobIdentity;
use Batchelor\WebService\Types\JobState;
use Batchelor\WebService\Types\JobStatus;
use Batchelor\WebService\Types\QueuedJob;
use InvalidArgumentException;
use IteratorAggregate;
use LogicException;
use Traversable;

/**
 * The state queue.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
abstract class State implements Inspector, Mutator, IteratorAggregate
{

        /**
         * The queue name.
         * @var string 
         */
        private $_queue;
        /**
         * The cache backend.
         * @var Storage 
         */
        private $_cache;

        /**
         * Constructor.
         * 
         * @param Storage $cache The cache backend.
         * @param string $queue The queue name.
         */
        protected function __construct(Storage $cache, string $queue)
        {
                $this->_cache = $cache;
                $this->_queue = $queue;
        }

        /**
         * {@inheritdoc}
         */
        public function count(): int
        {
                return $this->getCache("count", 0);
        }

        /**
         * {@inheritdoc}
         */
        public function index(): int
        {
                return $this->getCache("index", 1);
        }

        /**
         * {@inheritdoc}
         */
        public function queue(): array
        {
                return $this->getCache("queue", []);
        }

        /**
         * {@inheritdoc}
         */
        public function addStatus(JobIdentity $identity, JobStatus $status)
        {
                $this->increment("index");
                $this->increment("count");

                $identity->jobid = $this->index();
                $this->setStatus($identity, $status);
        }

        /**
         * {@inheritdoc}
         */
        public function removeStatus(JobIdentity $identity)
        {
                $this->decrement("count");

                $queue = $this->getCache("queue");
                unset($queue[$identity->jobid]);
                $this->setCache("queue", $queue);
        }

        /**
         * {@inheritdoc}
         */
        public function hasStatus(JobIdentity $identity): bool
        {
                return in_array($identity->jobid, $this->getList());
        }

        /**
         * {@inheritdoc}
         */
        public function getStatus(JobIdentity $identity): JobStatus
        {
                $queue = $this->getCache("queue");
                return $queue[$identity->jobid];
        }

        /**
         * {@inheritdoc}
         */
        public function setStatus(JobIdentity $identity, JobStatus $status)
        {
                $queue = $this->getCache("queue");
                $queue[$identity->jobid] = $status;
                $this->setCache("queue", $queue);
        }

        /**
         * {@inheritdoc}
         */
        public function isEmpty(): bool
        {
                return $this->count() == 0;
        }

        /**
         * {@inheritdoc}
         */
        public function getList(): array
        {
                return array_keys($this->queue());
        }

        /**
         * Get iterator for queue.
         * @return Traversable
         */
        public function getIterator(): Traversable
        {
                return new ArrayIterator($this->queue());
        }

        /**
         * Get cache data.
         * 
         * @param string $what The cached entry name.
         * @param mixed $default The default value if missing.
         * @return int|array
         */
        private function getCache(string $what, $default = false)
        {
                $cache = $this->_cache;
                $cname = sprintf("schedule-%s-%s", $this->_queue, $what);

                if ($cache->exists($cname)) {
                        return $cache->read($cname);
                } else {
                        return $default;
                }
        }

        /**
         * Set cache data.
         * 
         * @param string $what The cached entry name.
         * @param int|array $data The cache data.
         */
        private function setCache(string $what, $data)
        {
                $cache = $this->_cache;
                $cname = sprintf("schedule-%s-%s", $this->_queue, $what);

                $cache->save($cname, $data);
        }

        /**
         * Increment counter value.
         * @param string $counter The counter name.
         */
        private function increment(string $counter)
        {
                $this->setCache(
                    $counter, $this->getCache($counter, 0) + 1
                );
        }

        /**
         * Decrement counter value.
         * @param string $counter The counter name.
         */
        private function decrement(string $counter)
        {
                $this->setCache(
                    $counter, $this->getCache($counter, 0) - 1
                );
        }

        /**
         * Set job state.
         * 
         * Throws an invalid argument exception if state transition is invalid,
         * for example setting state to pending on an already running job. 
         * 
         * Transitions is always from pending -> running -> finished where last 
         * can have different state (i.e. finished with warning). A finished job
         * can also transition to pending again (restart), which will complete 
         * the state cycle.
         * 
         * @param JobIdentity $identity The job identity,
         * @param JobState $state The job state.
         * @throws InvalidArgumentException
         */
        public function setState(JobIdentity $identity, JobState $state)
        {
                if (!$this->hasStatus($identity)) {
                        throw new LogicException("The job identity is missing in this state queue");
                }

                switch (($value = $state->getValue())) {
                        case JobState::PENDING:
                        case JobState::WAITING:
                                (new Migration())
                                    ->setSource($this)
                                    ->setTarget(new Pending($this->_cache))
                                    ->moveTo($identity, $state);
                                break;
                        case JobState::RUNNING:
                                (new Migration())
                                    ->setSource($this)
                                    ->setTarget(new Running($this->_cache))
                                    ->moveTo($identity, $state);
                                break;
                        case JobState::FINISHED:
                        case JobState::SUCCESSS:
                        case JobState::WARNING:
                        case JobState::ERROR:
                        case JobState::CRASHED:
                                (new Migration())
                                    ->setSource($this)
                                    ->setTarget(new Finished($this->_cache))
                                    ->moveTo($identity, $state);
                                break;
                        default:
                                throw new InvalidArgumentException("Invalid state $value encountered in finished state queue");
                }
        }

        /**
         * Get runtime for identity.
         * 
         * @param JobIdentity $identity The job identity.
         * @return Runtime
         */
        public function getRuntime(JobIdentity $identity): Runtime
        {
                $s = $this->getStatus($identity);
                $t = (new Tasks($this->_cache))
                    ->getTask($identity);

                return new Runtime(
                    new QueuedJob($t->identity, $s), $t->data
                );
        }

}
