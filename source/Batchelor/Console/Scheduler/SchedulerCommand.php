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

use Batchelor\Queue\Task\Runtime;
use Batchelor\Queue\Task\Scheduler;
use Batchelor\Queue\Task\Scheduler\State\Inspector;
use Batchelor\WebService\Types\JobData;
use Batchelor\WebService\Types\JobIdentity;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The command for scheduled job viewer.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class SchedulerCommand extends Command
{

        protected function configure()
        {
                parent::configure();

                $this->setName("scheduler");
                $this->setDescription("Scheduled job viewer");

                $this->addOption("list", "l", InputOption::VALUE_NONE, "List schedule summary");
                $this->addOption("pending", "P", InputOption::VALUE_NONE, "List pending jobs");
                $this->addOption("running", "R", InputOption::VALUE_NONE, "List running jobs");
                $this->addOption("finished", "F", InputOption::VALUE_NONE, "List finished jobs");
                $this->addOption("all", "A", InputOption::VALUE_NONE, "List all jobs (pending, running and finished)");

                $this->addOption("add", "a", InputOption::VALUE_REQUIRED, "Add job to scheduler");
                $this->addOption("remove", "r", InputOption::VALUE_REQUIRED, "Delete job to scheduler");
                $this->addOption("show", "s", InputOption::VALUE_REQUIRED, "Show job details");
                $this->addOption("example", "e", InputOption::VALUE_REQUIRED, "Generate example input data");

                $this->addusage("--list [--pending] [--running] [--finished] [-v]");
                $this->addusage("--add=data|--remove=jobid|--show=jobid");
                $this->addusage("--example={add|remove|show}");
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
                if ($input->getOption("list")) {
                        $this->listJobs($input, $output);
                }
                if ($input->getOption("add")) {
                        $this->addJob($output, json_decode(
                                $input->getOption("add"), true
                        ));
                }
                if ($input->getOption("remove")) {
                        $this->removeJob($output, $input->getOption("remove"));
                }
                if ($input->getOption("show")) {
                        $this->showJob($output, $input->getOption("show"));
                }
                if ($input->getOption("example")) {
                        $this->exampleData($output, $input->getOption("example"));
                }
        }

        private function listJobs(InputInterface $input, OutputInterface $output)
        {
                $scheduler = new Scheduler();
                $summary = $scheduler->getSummary();

                $output->writeln("Summary");
                $output->writeln("-----------------------------");
                $output->writeln(sprintf("\tTimezone: %s", $summary->timezone));
                $output->writeln(sprintf("\t   Count: %s", $summary->count));
                $output->writeln(sprintf("\t   Index: %s", $summary->index));
                $output->writeln(sprintf("\t Pending: %s", $summary->pending));
                $output->writeln(sprintf("\t Running: %s", $summary->running));

                if ($input->getOption("all")) {
                        $input->setOption("pending", true);
                        $input->setOption("running", true);
                        $input->setOption("finished", true);
                }

                if ($input->getOption("pending") || $output->isVerbose()) {
                        $this->listQueue($output, $scheduler->getPending());
                }
                if ($input->getOption("running") || $output->isVerbose()) {
                        $this->listQueue($output, $scheduler->getRunning());
                }
                if ($input->getOption("finished") || $output->isVeryVerbose()) {
                        $this->listQueue($output, $scheduler->getFinished());
                }
        }

        private function listQueue(OutputInterface $output, Inspector $queue)
        {
                $output->writeln("");
                $output->writeln(sprintf("%s:", $queue->getName()));
                $output->writeln("-----------------------------");

                if ($queue->isEmpty()) {
                        $output->writeln("\tJob channel is empty");
                        return;
                }

                $format = "%-10s%-25s%s";

                $output->writeln(sprintf($format, "JobID:", "Time:", "Status:"));
                $output->writeln("");

                foreach ($queue as $jobid => $status) {
                        $output->writeln(sprintf(
                                $format, $jobid, strftime("%x %X", $status->stamp), $status->state
                        ));
                }
        }

        private function addJob(OutputInterface $output, array $data)
        {
                $scheduler = new Scheduler();

                $jobdata = new JobData(...array_values($data['data']));
                $runtime = $scheduler->makeRuntime($jobdata);
                $runtime->hostid = (new \Batchelor\System\Service\Hostid())->getValue();

                $scheduler->pushJob($runtime);

                $output->writeln(sprintf("Added job: %s [%s]", $runtime->meta->identity->jobid, strftime("%x %X", $runtime->meta->status->stamp)));
        }

        private function removeJob(OutputInterface $output, string $data)
        {
                $identity = new JobIdentity($data, "");
                $scheduler = new Scheduler();

                if (!$scheduler->hasJob($identity)) {
                        return $output->writeln("Job $data was not found");
                }

                $scheduler->removeJob($identity);
                $output->writeln("Removed job $data");
        }

        private function showJob(OutputInterface $output, string $data)
        {
                $identity = new JobIdentity($data, "");
                $scheduler = new Scheduler();

                if (!$scheduler->hasJob($identity)) {
                        return $output->writeln("Job $data is missing");
                }

                print_r($scheduler->getJob($identity));
        }

        private function exampleData(OutputInterface $output, string $option)
        {
                switch ($option) {
                        case "add":
                                $output->writeln(json_encode(
                                        $this->exampleRuntime()
                                ));
                                break;
                        case "remove":
                        case "show";
                                $output->writeln(json_encode(
                                        $this->exampleJobIdentity()
                                ));
                                break;
                        default:
                                throw new InvalidArgumentException("Invalid option $option for example");
                }
        }

        private function exampleRuntime(): Runtime
        {
                return (new Scheduler)
                        ->makeRuntime(
                            new JobData("input data", "data")
                );
        }

        private function exampleJobIdentity(): JobIdentity
        {
                return new JobIdentity("123", "456");
        }

}
