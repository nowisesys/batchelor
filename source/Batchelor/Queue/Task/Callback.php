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
use Batchelor\Queue\Task\Execute\Command;
use Batchelor\Queue\Task\Execute\Monitor;
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
 * $logger = $callback->getLogger();    // Get message logger.
 *      ...                             // Do some work in task...
 * $logger->info("Finished");
 * $logger->setStatus(JobState::SUCCESS);
 * </code>
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Callback
{

        /**
         * The job status object.
         * @var JobStatus  
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
        public function __construct(JobStatus $status)
        {
                $this->_status = $status;
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
                $this->_status->state = $state->getValue();
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
                return (new Monitor)
                        ->execute(
                            Command::create($cmd, $env), $this->_logger
                );
        }

        public function runProcess(Selectable $command)
        {
                (new Monitor($command))->execute();
        }

}
