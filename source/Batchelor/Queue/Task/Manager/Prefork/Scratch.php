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

namespace Batchelor\Queue\Task\Manager\Prefork;

use Batchelor\Cache\Service;
use Batchelor\Cache\Storage;
use RuntimeException;
use SyncReaderWriter;

/**
 * The scratch board.
 * 
 * Data is maintained in two arrays (running and finished tasks) in cache. Access 
 * to either one is protected by a synchronization object protecting it from being
 * modified while reading.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Scratch
{

        /**
         * The application cache,
         * @var Storage 
         */
        private $_cache;

        /**
         * Constructor.
         * @throws RuntimeException
         */
        public function __construct()
        {
                if (!extension_loaded("sync")) {
                        throw new RuntimeException("The sync extension is not loaded");
                }
                
                $this->_cache = new Service();
        }

        /**
         * Get number of running tasks.
         * @return int
         */
        public function numRunning(): int
        {
                $cname = "task-running";
                $qsync = $this->getSyncLock($cname);

                try {
                        $qsync->readlock();
                        return count($this->getContent($cname));
                } finally {
                        $qsync->readunlock();
                }
        }

        /**
         * Add running task.
         * @param int $pid The process ID (PID/TID).
         * @param string $job The job identity.
         */
        public function addRunning(int $pid, string $job)
        {
                $cname = "task-running";
                $qsync = $this->getSyncLock($cname);

                try {
                        $qsync->writelock();

                        $content = $this->getContent($cname);
                        $content[$pid] = $job;
                        $this->setContent($cname, $content);
                } finally {
                        $qsync->writeunlock();
                }
        }

        /**
         * Remove running task.
         * @param int $pid The process ID (PID/TID).
         */
        public function removeRunning(int $pid)
        {
                $cname = "task-running";
                $qsync = $this->getSyncLock($cname);

                try {
                        $qsync->writelock();

                        $content = $this->getContent($cname);
                        unset($content[$pid]);
                        $this->setContent($cname, $content);
                } finally {
                        $qsync->writeunlock();
                }
        }

        /**
         * Get running tasks.
         * @return array
         */
        public function getRunning(): array
        {
                $cname = "task-running";
                $qsync = $this->getSyncLock($cname);

                try {
                        $qsync->readlock();
                        return $this->getContent($cname);
                } finally {
                        $qsync->readunlock();
                }
        }

        /**
         * Set running tasks.
         * @param array $data The running tasks.
         */
        public function setRunning(array $data)
        {
                $cname = "task-running";
                $qsync = $this->getSyncLock($cname);

                try {
                        $qsync->writelock();
                        $this->setContent($cname, $data);
                } finally {
                        $qsync->writeunlock();
                }
        }

        /**
         * Add finished task.
         * @param array $data The task data.
         */
        public function addFinished(array $data)
        {
                $cname = "task-finished";
                $qsync = $this->getSyncLock($cname);

                try {
                        $qsync->writelock();

                        $content = $this->getContent($cname);
                        $content[] = $data;
                        $this->setContent($cname, $content);
                } finally {
                        $qsync->writeunlock();
                }
        }

        /**
         * Check if finished tasks exists.
         * @return bool
         */
        public function hasFinished(): bool
        {
                $cname = "task-finished";
                $qsync = $this->getSyncLock($cname);

                try {
                        $qsync->readlock();
                        return count($this->getContent($cname)) > 0;
                } finally {
                        $qsync->readunlock();
                }
        }

        /**
         * Get finished tasks.
         * @return array
         */
        public function getFinished(): array
        {
                $cname = "task-finished";
                $qsync = $this->getSyncLock($cname);

                try {
                        $qsync->readlock();
                        return $this->getContent($cname);
                } finally {
                        $qsync->readunlock();
                }
        }

        /**
         * Set finished tasks.
         * @param array $data The finished tasks.
         */
        public function setFinished(array $data)
        {
                $cname = "task-finished";
                $qsync = $this->getSyncLock($cname);

                try {
                        $qsync->writelock();
                        $this->setContent($cname, $data);
                } finally {
                        $qsync->writeunlock();
                }
        }

        /**
         * Read cached data.
         * 
         * @param string $ckey The cache key.
         * @return array
         */
        private function getContent(string $ckey): array
        {
                if (!$this->_cache->exists($ckey)) {
                        return [];
                } else {
                        return $this->_cache->read($ckey);
                }
        }

        /**
         * Set cached data.
         * 
         * @param string $ckey The cache key.
         * @param array $data The cached data.
         */
        private function setContent(string $ckey, array $data)
        {
                $this->_cache->save($ckey, $data);
        }

        /**
         * Get synchronize read/write lock.
         * 
         * Returns a sync read/write object that can be used to protect other
         * threads from entering a critical section. The lock is bound to this
         * manager name.
         * 
         * @param string $name The lock name.
         * @return SyncReaderWriter
         * @see http://php.net/manual/en/class.syncreaderwriter.php
         */
        public function getSyncLock(string $name = "lock"): SyncReaderWriter
        {
                return new SyncReaderWriter(
                    sprintf("manager-prefork-%s", $name)
                );
        }

}
