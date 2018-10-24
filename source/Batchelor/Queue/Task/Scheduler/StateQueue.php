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
use Batchelor\Queue\Task\Scheduler\Inspector;
use IteratorAggregate;
use SyncReaderWriter;
use Traversable;

/**
 * The state queue.
 * 
 * Represent the queue i.e. for an hostid or running jobs queue. The ident is the 
 * name is used to identify the queue in the cache.
 * 
 * The data representation of the queue is a simple array keyed by job identity
 * having an array of volatile data (a property bag). The number of items in the
 * queue is expected to be relative small, so the serialization should not have a
 * large impact on performance.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class StateQueue implements Inspector, IteratorAggregate
{

        /**
         * The queue name.
         * @var string 
         */
        private $_ident;
        /**
         * The cache backend.
         * @var Storage 
         */
        private $_cache;
        /**
         * The read/write lock.
         * @var SyncReaderWriter 
         */
        private $_qsync;

        /**
         * Constructor.
         * 
         * @param string $ident The queue name.
         * @param Storage $cache The cache backend.
         */
        public function __construct(string $ident, Storage $cache)
        {
                $this->_ident = $ident;
                $this->_cache = $cache;

                $this->_qsync = new SyncReaderWriter($this->getCacheKey());
        }

        /**
         * {@inheritdoc}
         */
        public function getName(): string
        {
                return $this->_ident;
        }

        /**
         * {@inheritdoc}
         */
        public function isEmpty(): bool
        {
                return $this->getCounter()->getSize() == 0;
        }

        /**
         * {@inheritdoc}
         */
        public function getSize(): int
        {
                return $this->getCounter()->getSize();
        }

        /**
         * Add state to queue.
         * 
         * @param string $job The job ID.
         * @param State $state The job state.
         */
        public function addState(string $job, State $state)
        {
                $content = $this->getContent();
                $content[$job] = $state;
                $this->setContent($content);
        }

        /**
         * {@inheritdoc}
         */
        public function getState(string $job): State
        {
                while (true) {
                        $content = $this->getContent();
                        if (isset($content[$job])) {
                                return $content[$job];
                        } else {
                                sleep(1);
                        }
                }
        }

        /**
         * Remove state from queue.
         * 
         * @param string $job The job ID.
         */
        public function removeState(string $job)
        {
                $content = $this->getContent();
                unset($content[$job]);
                $this->setContent($content);
        }

        /**
         * {@inheritdoc}
         */
        public function hasState(string $job): bool
        {
                $content = $this->getContent();
                return isset($content[$job]);
        }

        /**
         * {@inheritdoc}
         */
        public function getFirst(): string
        {
                $content = $this->getContent();
                return key($content);
        }

        /**
         * {@inheritdoc}
         */
        public function getContent(): array
        {
                $cname = $this->getCacheKey();
                $cache = $this->_cache;

                try {
                        $this->_qsync->readlock();

                        if ($cache->exists($cname)) {
                                return $cache->read($cname);
                        } else {
                                return [];
                        }
                } finally {
                        $this->_qsync->readunlock();
                }
        }

        /**
         * Set queue content.
         * @param array $content The content array.
         */
        public function setContent(array $content)
        {
                try {
                        $this->_qsync->writelock();

                        $this->_cache->save($this->getCacheKey(), $content);
                        $this->getCounter()->setSize(count($content));
                } finally {
                        $this->_qsync->writeunlock();
                }
        }

        /**
         * Get cache queue.
         * @return string
         */
        private function getCacheKey(): string
        {
                return sprintf("scheduler-%s-queue", $this->_ident);
        }

        /**
         * Get counter for queue.
         * 
         * For performance reasons the counter (number of items in queue) is
         * maintained in a separate object.
         * 
         * @return Counter
         */
        private function getCounter(): Counter
        {
                return new Counter($this->_ident, $this->_cache);
        }

        /**
         * {@inheritdoc}
         */
        public function getIterator(): Traversable
        {
                return new ArrayIterator($this->getContent());
        }

}
