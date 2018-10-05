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

namespace Batchelor\Console\Scheduler;

use Batchelor\Logging\Target\File as FileLogger;
use Batchelor\Queue\Task\Processor;
use Batchelor\System\Process\Runner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The command for scheduled job processor.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class ProcessorCommand extends Command
{

        protected function configure()
        {
                parent::configure();

                $this->setName("processor");
                $this->setDescription("Scheduled job processor");

                $this->addOption("user", "U", InputOption::VALUE_REQUIRED, "The user for process");
                $this->addOption("group", "G", InputOption::VALUE_REQUIRED, "The group for process");
                $this->addOption("pidfile", "p", InputOption::VALUE_REQUIRED, "Create process ID (PID) file");
                $this->addOption("logfile", "l", InputOption::VALUE_REQUIRED, "Write events to logfile");
                $this->addOption("foreground", "k", InputOption::VALUE_NONE, "Don't run as daemon process");
                $this->addOption("worker", "w", InputOption::VALUE_REQUIRED, "The number of worker to fork");
                $this->addOption("debug", "d", InputOption::VALUE_NONE, "Enable debug mode");

                $this->addusage("[--user=batchelor] [--group=daemon] [--pidfile=/var/run/batchelor.pid]");
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
                $processor = new Processor();

                $daemon = new Runner($processor);

                if ($input->getOption("worker")) {
                        $processor->setWorkers($input->getOption("worker"));
                }
                if ($input->getOption("user")) {
                        $daemon->setProcessUser($input->getOption("user"));
                }
                if ($input->getOption("group")) {
                        $daemon->setProcessGroup($input->getOption("group"));
                }
                if ($input->getOption("foreground")) {
                        $daemon->setForeground();
                }
                if ($input->getOption("pidfile")) {
                        $daemon->setProcessFile($input->getOption("pidfile"));
                }
                if ($input->getOption("logfile")) {
                        $daemon->setLogger(new FileLogger(
                            $input->getOption("logfile")
                        ));
                }

                $daemon->getLogger()->setThreshold(LOG_DEBUG);

                if ($input->getOption("debug") || $output->isDebug()) {
                        $daemon->getLogger()->setThreshold(LOG_DEBUG + 1);
                } elseif ($output->isVeryVerbose()) {
                        $daemon->getLogger()->setThreshold(LOG_DEBUG);
                } elseif ($output->isVerbose()) {
                        $daemon->getLogger()->setThreshold(LOG_DEBUG - 1);
                }
                if ($input->getOption("quiet") || $output->isQuiet()) {
                        $daemon->getLogger()->setThreshold(LOG_NOTICE);
                }

                $daemon->execute();
        }

}
