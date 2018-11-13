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

namespace Batchelor\Queue\Task\Manager\Shared;

use Batchelor\Queue\Task;
use Batchelor\Queue\Task\Runtime;
use Batchelor\System\Component;
use Batchelor\WebService\Types\JobState;
use Throwable;

/**
 * Common class for running task.
 *
 * This class should be used from the task manager to process a single task. When
 * invoked, the caller should have arrange a robust context in which calling exit() 
 * or die() don't affect the main thread.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class TaskRunner extends Component
{

        /**
         * Run task process.
         * 
         * @param Runtime $runtime The task runtime.
         */
        public function runTask(Runtime $runtime)
        {
                $task = $this->getTask($runtime->data->task);
                $logs = $this->getLogger($runtime);
                $info = $runtime->getLogger();

                register_shutdown_function(function() use($logs) {
                        $logs->stop();
                        $logs->flush();
                });

                try {
                        $logs->start();
                        $task->run($runtime, $info);
                } catch (Throwable $exception) {
                        $runtime->getCallback()->setStatus(JobState::CRASHED());
                        $logs->logException($exception);
                        $logs->flush();
                }
        }

        private function getTask(string $processor): Task
        {
                if ($this->processor->hasProcesor($processor)) {
                        return $this->processor->getProcessor($processor);
                }
        }

        private function getLogger(Runtime $runtime): TaskLogger
        {
                $logs = new TaskLogger();

                $logs->setLogger($runtime->getLogger());
                $logs->setLogfile($runtime->getLogfile());

                return $logs;
        }

}
