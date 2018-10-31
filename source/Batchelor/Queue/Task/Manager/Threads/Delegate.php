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

namespace Batchelor\Queue\Task\Manager\Threads;

use Batchelor\Queue\Task\Manager\Shared\TaskRunner;
use Batchelor\Queue\Task\Manager\Threads;
use Batchelor\Queue\Task\Runtime;
use Thread;
use Throwable;

/**
 * The thread <-> task delegate.
 * 
 * Extends thread to be runnable by worker. The run() method is invoked by 
 * running thread (think java). The actual task processing is delegated to
 * task runner.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Delegate extends Thread
{

        /**
         * The job runtime.
         * @var Runtime 
         */
        private $_runtime;
        /**
         * Callback on complete.
         * @var Threads 
         */
        private $_manager;
        /**
         * The task runner.
         * @var TaskRunner 
         */
        private $_execute;

        /**
         * Construct.
         * @param Runtime $runtime The job runtime.
         */
        public function __construct(Runtime $runtime, Threads $manager)
        {
                $this->_runtime = $runtime;
                $this->_manager = $manager;
                $this->_execute = new TaskRunner();
        }

        /**
         * {@inheritdoc}
         */
        public function run()
        {
                try {
                        $this->_execute->runTask($this->_runtime);
                        $this->_manager->setFinished([
                                $this->_runtime->job, self::getCurrentThreadId(), 0
                        ]);
                } catch (Throwable $exception) {
                        error_log(print_r($exception, true));
                        $this->_manager->setFinished([
                                $this->_runtime->job, self::getCurrentThreadId(), 1
                        ]);
                }
        }

}
