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
use Batchelor\System\Component;
use Batchelor\System\Process\Daemonized;
use RuntimeException;

/**
 * The scheduled task processor.
 * 
 * Should be run as a command line (CLI) task that consumes queued jobs from the 
 * job scheduler. Queries the processor service for a matching task processor to 
 * handle the queued job.
 * 
 * This class is not executing jobs. Instead it's delegating everything related
 * to running task to its worker manager that is running in the same main thread,
 * but executing tasks using threads/forked processes.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Processor extends Component implements Daemonized
{

        /**
         * The time to exit flag.
         * @var bool 
         */
        private $_done;
        /**
         * The number of workers.
         * @var int 
         */
        private $_workers = 3;

        /**
         * Constructor.
         * 
         * @throws RuntimeException
         */
        public function __construct()
        {
                if (PHP_SAPI != "cli") {
                        throw new RuntimeException("The processor should only be run in CLI mode.");
                }
        }

        public function setWorkers(int $num)
        {
                $this->_workers = $num;
        }

        public function prepare(Logger $logger)
        {
                $this->_done = false;
        }

        public function execute(Logger $logger)
        {
                $this->run($logger);
        }

        public function terminate(Logger $logger)
        {
                $logger->debug("Got terminate signal (setting exit flag)");
                $this->_done = true;
        }

        public function finished(): bool
        {
                return $this->_done;
        }

        /**
         * Run queued jobs.
         */
        protected function run(Logger $logger)
        {
                $scheduler = new Scheduler();

                $logger->info("Ready to process jobs (%d workers)", [$this->_workers]);
                while (!$this->finished()) {
                        $this->loop($logger, $scheduler);
                }
                $logger->info("Finished process jobs");
        }

        private function loop(Logger $logger, Scheduler $scheduler)
        {
                if (function_exists("pcntl_signal_dispatch")) {
                        pcntl_signal_dispatch();
                }

                if ($this->finished()) {
                        return;
                } else {
                        $this->poll($logger, $scheduler);
                }
        }

        private function poll(Logger $logger, Scheduler $scheduler)
        {
                $logger->debug("Polling for jobs");

                if ($scheduler->hasJobs()) {
                        $this->process($logger, $scheduler);
                } else {
                        sleep(10);
                }
        }

        private function process(Logger $logger, Scheduler $scheduler)
        {
                if (($runtime = $scheduler->popJob())) {
                        // TODO: Create and execute task.
                }
        }

}
