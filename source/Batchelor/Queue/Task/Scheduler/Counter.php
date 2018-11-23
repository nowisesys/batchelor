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
use RuntimeException;
use SyncReaderWriter;

/**
 * Counter for state queue.
 * 
 * This class helps keeping track on the state queue size without require the
 * queue consumer to actually open the queue itself. Uses synchronize locks to
 * coordinate read/write operations.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Counter
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
         * Constructor.
         * 
         * @param string $ident The queue name.
         * @param Storage $cache The cache backend.
         */
        public function __construct(string $ident, Storage $cache)
        {
                if (!extension_loaded("sync")) {
                        throw new RuntimeException("The sync extension is not loaded");
                }

                $this->_ident = $ident;
                $this->_cache = $cache;
        }

        /**
         * Increment counter value.
         */
        public function increment()
        {
                $qsync = $this->getSyncLock();

                try {
                        $qsync->writelock();
                        $value = $this->getValue();
                        $this->setValue( ++$value);
                } finally {
                        $qsync->writeunlock();
                }
        }

        /**
         * Decrement counter value.
         */
        public function decrement()
        {
                $qsync = $this->getSyncLock();

                try {
                        $qsync->writelock();
                        $value = $this->getValue();
                        $this->setValue( --$value);
                } finally {
                        $qsync->writeunlock();
                }
        }

        /**
         * Get counter value.
         * @return int
         */
        public function getSize(): int
        {
                $qsync = $this->getSyncLock();

                try {
                        $qsync->readlock();
                        return $this->getValue();
                } finally {
                        $qsync->readunlock();
                }
        }

        /**
         * Set counter value.
         * @param int $size The number of items.
         */
        public function setSize(int $size)
        {
                $qsync = $this->getSyncLock();

                try {
                        $qsync->writelock();
                        $this->setValue($size);
                } finally {
                        $qsync->writeunlock();
                }
        }

        /**
         * Get counter value.
         * @return int
         */
        private function getValue(): int
        {
                $cname = $this->getCacheKey();
                $cache = $this->_cache;

                if ($cache->exists($cname)) {
                        return $cache->read($cname);
                } else {
                        return 0;
                }
        }

        /**
         * Set counter value.
         * @param int $value The counter value.
         */
        private function setValue(int $value)
        {
                if ($value < 0) {
                        return;
                }

                $cname = $this->getCacheKey();
                $cache = $this->_cache;

                $cache->save($cname, $value);
        }

        /**
         * Get cache queue.
         * @return string
         */
        private function getCacheKey(): string
        {
                return sprintf("%s-count", $this->_ident);
        }

        /**
         * Get synchronize read/write lock.
         * 
         * Returns a sync read/write object that can be used to protect other
         * threads from entering a critical section. The lock is bound to this
         * counter name.
         * 
         * @param string $name The lock name.
         * @return SyncReaderWriter
         * @see http://php.net/manual/en/class.syncreaderwriter.php
         */
        public function getSyncLock(string $name = "lock"): SyncReaderWriter
        {
                return new SyncReaderWriter(
                    sprintf("%s-count-%s", $this->_ident, $name)
                );
        }

}
