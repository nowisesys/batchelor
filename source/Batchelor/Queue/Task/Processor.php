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
use Batchelor\Queue\Task\Manager\Prefork;
use Batchelor\Queue\Task\Manager\Threads;
use Batchelor\System\Component;
use Batchelor\System\Process\Daemonized;
use Batchelor\WebService\Types\JobState;
use InvalidArgumentException;
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
         * The work manager type.
         * @var string 
         */
        private $_manager = "prefork";
        /**
         * The work manager.
         * @var Manager 
         */
        private $_runner;
        /**
         * The poll interval.
         * @var int 
         */
        private $_poll = 5;

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

        /**
         * Set number of workers (threads or processes).
         * @param int $num The number of workers.
         */
        public function setWorkers(int $num)
        {
                $this->_workers = $num;
        }

        /**
         * Set worker manager type.
         * @param string $type The manager type.
         */
        public function setManager(string $type)
        {
                $this->_manager = $type;
        }

        /**
         * Set polling interval.
         * @param int $interval The polling interval.
         */
        public function setPolling(int $interval)
        {
                $this->_poll = $interval;
        }

        /**
         * {@inheritdoc}
         */
        public function prepare(Logger $logger)
        {
                $this->_done = false;
                $this->_runner = $this->getManager();
        }

        /**
         * {@inheritdoc}
         */
        public function execute(Logger $logger)
        {
                $this->run($logger);
        }

        /**
         * {@inheritdoc}
         */
        public function terminate(Logger $logger)
        {
                $logger->debug("Got terminate signal (setting exit flag)");
                $this->_done = true;
        }

        /**
         * {@inheritdoc}
         */
        public function finished(): bool
        {
                return $this->_done;
        }

        protected function run(Logger $logger)
        {
                $logger->debug("Starting up work processor...");

                $scheduler = new Scheduler();

                $manager = $this->_runner;
                $workers = $this->_workers;
                $polling = $this->_poll;

                $logger->info("Ready to process jobs (%d workers:%s:%d sec poll)", [$workers, $manager->getType(), $polling]);
                while (!$this->finished()) {
                        $this->loop($logger, $scheduler);
                }
                $logger->info("Finished process jobs");

                while (!$manager->isIdle()) {
                        $logger->debug("Collecting child processes");
                        $this->setResult($scheduler, $manager->getChildren());
                }

                $logger->debug("Closed work processor");
        }

        private function loop(Logger $logger, Scheduler $scheduler)
        {
                if (function_exists("pcntl_signal_dispatch")) {
                        pcntl_signal_dispatch();
                }

                if (!$this->finished()) {
                        $stime = microtime(true);

                        $this->poll($logger, $scheduler, $this->_runner);

                        $etime = microtime(true);
                        $dtime = ($etime - $stime);
                        $ttime = pow(10, 6) * ($this->_poll - $dtime);

                        if ($ttime > 0) {
                                usleep($ttime);
                        }
                        if ($dtime > 1) {
                                $logger->warning("Slow poll processing detected (%f sec)", [$dtime]);
                        }
                }
        }

        private function poll(Logger $logger, Scheduler $scheduler, Manager $manager)
        {
                $logger->debug("Polling for jobs");

                if ($manager->isIdle() == false) {
                        $logger->debug("Collecting child processes");
                        $this->setResult($scheduler, $manager->getChildren());
                }
                if ($manager->isBusy()) {
                        $logger->debug("Manager is busy");
                        return;
                }

                while ($scheduler->hasJobs() && $manager->isBusy() == false) {
                        $this->process($logger, $scheduler, $manager);
                }

                if ($manager->isIdle()) {
                        return;
                }
        }

        private function process(Logger $logger, Scheduler $scheduler, Manager $manager)
        {
                if (($runtime = $scheduler->popJob())) {
                        $logger->info("Running job %s", [$runtime->job]);
                        $runtime->setCallback(new class($scheduler, $runtime) extends Callback {

                                private $scheduler;
                                private $runtime;

                                public function __construct(Scheduler $scheduler, Runtime $runtime)
                                {
                                        parent::__construct();

                                        $this->scheduler = $scheduler;
                                        $this->runtime = $runtime;
                                }

                                protected function onStatus(JobState $state)
                                {
                                        $this->scheduler->setFinished($this->runtime->job, $state);
                                        exit(0);
                                }
                        });
                        $manager->addJob($runtime);
                }
        }

        /**
         * Get work manager object.
         * 
         * @return Manager
         * @throws InvalidArgumentException
         */
        private function getManager(): Manager
        {
                switch ($this->_manager) {
                        case 'threads':
                                return new Threads($this->_workers);
                        case 'prefork':
                                return new Prefork($this->_workers);
                        default:
                                throw new InvalidArgumentException("Unknown type of work manager $this->_manager");
                }
        }

        /**
         * Set job result state.
         * 
         * @param Scheduler $scheduler The job scheduler.
         * @param array $results The results array.
         */
        private function setResult(Scheduler $scheduler, array $results)
        {
                foreach ($results as $result) {
                        if ($result['code'] != 0) {
                                $scheduler->setFinished($result['job'], JobState::ERROR());
                        }
                }
        }

}
