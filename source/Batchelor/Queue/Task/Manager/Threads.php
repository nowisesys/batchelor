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

namespace Batchelor\Queue\Task\Manager;

use Batchelor\Queue\Task\Manager;
use Batchelor\Queue\Task\Manager\Threads\Autoloader;
use Batchelor\Queue\Task\Manager\Threads\Delegate;
use Batchelor\Queue\Task\Runtime;
use Pool;
use RuntimeException;
use const BATCHELOR_APP_ROOT;

/**
 * POSIX threads task executor.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Threads implements Manager
{

        /**
         * The worker pool.
         * @var Pool 
         */
        private $_pool;
        /**
         * The finished tasks.
         * @var array 
         */
        private $_done = [];

        /**
         * Constructor.
         * 
         * @param int $workers The number of workers.
         * @throws RuntimeException
         */
        public function __construct(int $workers)
        {
                if (!extension_loaded("pthreads")) {
                        throw new RuntimeException("The pthreads extension is not loaded");
                }

                $this->_pool = new Pool(
                    $workers, Autoloader::class, [BATCHELOR_APP_ROOT . '/vendor/autoload.php']
                );
        }

        /**
         * {@inheritdoc}
         */
        public function getType(): string
        {
                return "pthreads";
        }

        /**
         * {@inheritdoc}
         */
        public function addJob(Runtime $runtime)
        {
                $this->_pool->submit(new Delegate($runtime, $this));
        }

        /**
         * Called on finished task.
         * @param array $data The task result.
         */
        public function setFinished($data)
        {
                $this->_done[] = $data;
        }

        /**
         * {@inheritdoc}
         */
        public function getChildren(): array
        {
                try {
                        return $done = $this->_done;
                } finally {
                        $this->_done = [];
                }
        }

        /**
         * {@inheritdoc}
         */
        public function isBusy(): bool
        {
                return false;
        }

        /**
         * {@inheritdoc}
         */
        public function isIdle(): bool
        {
                return $this->_pool->collect() == 0;
        }

        /**
         * {@inheritdoc}
         */
        public function setWorkers(int $number)
        {
                $this->_pool->resize($number);
        }

}
