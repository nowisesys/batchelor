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

use Batchelor\Queue\Task\Manager;
use Batchelor\Queue\Task\Manager\Shared\TaskRunner;
use Batchelor\Queue\Task\Runtime;
use Throwable;

/**
 * Run forked process.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Delegate
{

        /**
         * The job runtime.
         * @var Runtime 
         */
        private $_runtime;
        /**
         * Callback on complete.
         * @var Manager 
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
        public function __construct(Runtime $runtime, Manager $manager)
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
                        $this->setStarting();
                        $this->_execute->runTask($this->_runtime);
                        $this->setFinished(0);
                } catch (Throwable $exception) {
                        error_log(print_r($exception, true));
                        $this->setFinished(1);
                } finally {
                        exit(0);        // Exit child process
                }
        }

        /**
         * Set task starting.
         */
        private function setStarting()
        {
                $this->_manager->onStarting([
                        'job' => $this->_runtime->job,
                        'pid' => posix_getpid()
                ]);
        }

        /**
         * Set task finished.
         * @param int $code The exit code.
         */
        private function setFinished(int $code)
        {
                $this->_manager->onFinished([
                        'job'  => $this->_runtime->job,
                        'pid'  => posix_getpid(),
                        'code' => $code
                ]);
        }

}
