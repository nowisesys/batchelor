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

use Batchelor\Logging\Logger;
use Batchelor\Queue\Task\Execute\Process;
use Batchelor\Queue\Task\Execute\Selectable;
use Batchelor\Queue\Task\Execute\Status;
use Batchelor\WebService\Types\JobData;
use Batchelor\WebService\Types\JobState;

/**
 * The task interaction interface.
 * 
 * Interface for interaction between running task, the task manager and the
 * job scheduler. Provides methods for logging, running processes and schedule
 * sub tasks.
 * 
 * <code>
 * $task->getLogger()->info("Starting");
 *      ...     // doing some work...
 * $task->getLogger()->info("Finished");
 * $task->setStatus(JobState::SUCCESS());
 * </code>
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
interface Interaction
{

        /**
         * Get message logger.
         * @return Logger The message logger.
         */
        function getLogger(): Logger;

        /**
         * Set message logger.
         * 
         * Call this method to replace the default in memory logger with for 
         * example a file logger or syslog.
         * 
         * @param Logger $logger The message logger.
         */
        function setLogger(Logger $logger);

        /**
         * Set job status.
         * 
         * <code>
         * $message->setStatus(JobState::ERROR());
         * </code>
         * 
         * @param JobState $state The job state.
         */
        function setStatus(JobState $state);

        /**
         * Run non-interactive command.
         * 
         * Execute command with optional environment variables and working 
         * directory. The command output is automatic captured and appended
         * to current message logger.
         * 
         * Returns the process status object from which exit code and whether
         * process was signaled can be detected.
         * 
         * @param string $cmd The command string.
         * @param array $env The environment variables.
         * @param string $cwd The working directory.
         * @return Status
         */
        function runCommand(string $cmd, array $env = null, string $cwd = null): Status;

        /**
         * Run selectable command.
         * 
         * The command is excuted and its process object is returned that can be
         * used to control the process, read output and status. Output streams 
         * from process is set non-blocking.
         * 
         * @param Selectable $command
         * @return Process 
         */
        function runProcess(Selectable $command): Process;

        /**
         * Add scheduled task.
         * 
         * Schedules a new task for later execution. This is equivalent to adding
         * a new job in the scheduler.
         * 
         * @param JobData $data The job data.
         */
        function newTask(JobData $data);
}
