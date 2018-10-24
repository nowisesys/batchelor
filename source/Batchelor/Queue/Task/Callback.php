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

use Batchelor\Logging\Format\DateTime;
use Batchelor\Logging\Logger;
use Batchelor\Logging\Target\Memory;
use Batchelor\Queue\Task\Execute\Capture;
use Batchelor\Queue\Task\Execute\Selectable;
use Batchelor\WebService\Types\JobState;
use Batchelor\WebService\Types\JobStatus;

/**
 * The message class.
 * 
 * Passed to executed task and used for communication between the task being
 * run and the job queue processor service. 
 *
 * <code>
 * $callback->getLogger()->info("Starting");
 *      ...     // doing some work...
 * $callback->getLogger()->info("Finished");
 * $callback->setStatus(JobState::SUCCESS());
 * </code>
 * 
 * Only set status if job has failed or has completed. If current task is part 
 * of a pipeline, then set status in last sub task.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Callback
{

        /**
         * The job status.
         * @var JobState  
         */
        private $_status;
        /**
         * The message logger.
         * @var Logger 
         */
        private $_logger;

        /**
         * Constructor.
         * @param JobStatus $status The job status object.
         */
        public function __construct()
        {
                $this->_status = JobState::NONE();
                $this->_logger = $this->useLogger();
        }

        /**
         * Set job status.
         * 
         * <code>
         * $message->setStatus(JobState::ERROR);
         * </code>
         * 
         * @param JobState $state The job state.
         */
        public function setStatus(JobState $state)
        {
                $this->_status = $state;
        }

        /**
         * Get job status.
         * @return JobState 
         */
        public function getStatus(): JobState
        {
                return $this->_status;
        }

        /**
         * Get message logger.
         * @return Logger The message logger.
         */
        public function getLogger(): Logger
        {
                return $this->_logger;
        }

        /**
         * Set message logger.
         * 
         * Call this method to replace the default in memory logger with for 
         * example a file logger or syslog.
         * 
         * @param Logger $logger The message logger.
         */
        public function setLogger(Logger $logger)
        {
                $this->_logger = $logger;
        }

        /**
         * Create message logger.
         * @return Memory
         */
        private function useLogger()
        {
                return new Memory([
                        'expand'   => "@datetime@: @message@ (@priority@)",
                        'datetime' => DateTime::FORMAT_HUMAN
                ]);
        }

        /**
         * Run non-interactive command.
         * 
         * The command is executed and output is captured. If an error occure,
         * then an runtime exception will be thrown. Returns the exit status
         * from command.
         * 
         * @param string $cmd The command string.
         * @param array $env The environment variables.
         * @return int The exit status
         */
        public function runCommand(string $cmd, array $env = null): int
        {
                Capture::create($this->_logger, $cmd, $env)->execute();
        }

        public function runProcess(Selectable $command)
        {
                (new Capture($command, $this->_logger))->execute();
        }

}
