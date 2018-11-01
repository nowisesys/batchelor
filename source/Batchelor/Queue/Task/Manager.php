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

/**
 * The worker manager.
 * 
 * Classes should implement this interface to provide task running functionality
 * that can be plugged into the processor class. 
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
interface Manager
{

        /**
         * Manager is busy at the moment.
         */
        function isBusy(): bool;

        /**
         * Manager is idle at the moment (no jobs running).
         */
        function isIdle(): bool;

        /**
         * Number of running tasks.
         */
        function getRunning(): int;

        /**
         * Add job to task pool.
         * @param Runtime $runtime The task runtime.
         */
        function addJob(Runtime $runtime);

        /**
         * Check if finished tasks can be collected.
         * @return bool 
         */
        function hasFinished(): bool;

        /**
         * Collect finished tasks.
         * 
         * Returns an array of zero or more entries, each one containg the 
         * process identity (PID/TID), the job ID and the exit code of the 
         * finished task:
         * 
         * <code>
         * $status = [
         *      'code' => 0,            // The process exit code
         *      'job'  => 143,          // The job ID (from scheduler)
         *      'pid'  => 23872         // The process ID (PID/TID)
         * ]
         * </code>
         * 
         * @return array The array of finished tasks.
         */
        function getFinished(): array;

        /**
         * Set number of workers.
         * @param int $number The number of workers.
         */
        function setWorkers(int $number);

        /**
         * Get manager type (i.e. prefork).
         */
        function getType(): string;

        /**
         * Called on started task.
         * @param array $data The task data.
         */
        function onStarting(array $data);

        /**
         * Called on finished task.
         * @param array $data The task data.
         */
        function onFinished(array $data);
}
