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

namespace Batchelor\Console\WebServiceClient;

use Batchelor\WebService\Types\JobIdentity;
use Batchelor\WebService\Types\QueueFilterResult;
use Batchelor\WebService\Types\QueueSortResult;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base class for web service client commands.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
abstract class BaseCommand extends Command
{

        protected function configure()
        {
                parent::configure();

                $this->addOption("base", null, InputOption::VALUE_OPTIONAL, "The base URL to web services", "http://localhost/batchelor/ws");
                $this->addOption("func", null, InputOption::VALUE_REQUIRED, "Execute the named function (see --func=list)");
                $this->addOption("file", null, InputOption::VALUE_REQUIRED, "Use file when posting data (see --post=file)");
                $this->addOption("params", null, InputOption::VALUE_REQUIRED, "JSON-encoded function parameters (e.g. {\"result\":1234.\"jobid\":99})");
                $this->addOption("trace", null, InputOption::VALUE_NONE, "Enable client request tracing");

                $this->addusage("--func=<method> --base=http://localhost/batchelor2/ws/soap");
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
                if ($input->getOption('func') == "list") {
                        $this->showFunctions($output);
                        exit(0);
                }
        }

        protected function showFunctions(OutputInterface $output)
        {
                $methods = new Methods();

                $methods->addType('identity', new JobIdentity("123", "456"));
                $methods->addType('sort', new QueueSortResult(QueueSortResult::STARTED));
                $methods->addType('filter', new QueueFilterResult(QueueFilterResult::FINISHED));

                $methods->addMethod('queue', [
                        'sort'   => $methods->getType('sort'),
                        'filter' => $methods->getType('filter')
                ]);
                $methods->addMethod('dequeue', [
                        'job' => $methods->getType('identity')
                ]);
                $methods->addMethod('enqueue', [
                        'indata' => 'string'
                ]);
                $methods->addMethod('select', [
                        'queue' => 'my-queue'
                ]);
                $methods->addMethod('opendir', null);
                $methods->addmethod('readdir', [
                        'job' => $methods->getType('identity')
                ]);
                $methods->addMethod('fopen', [
                        'job'  => $methods->getType('identity'),
                        'file' => 'input.txt'
                ]);
                $methods->addmethod('suspend', [
                        'job' => $methods->getType('identity')
                ]);
                $methods->addmethod('resume', [
                        'job' => $methods->getType('identity')
                ]);
                $methods->addmethod('stat', [
                        'job' => $methods->getType('identity')
                ]);
                $methods->addMethod('watch', [
                        'stamp' => 12458586
                ]);
                $methods->addMethod('version', null);

                $output->writeln("Types:");
                foreach ($methods->getTypes() as $name => $data) {
                        $output->writeln(sprintf("%15s =>\t%s", $name, json_encode($data)));
                }

                $output->writeln("Methods:");
                foreach ($methods->getMethods() as $method) {

                        $output->writeln(sprintf("\t--func=%s --params='%s'", $method, json_encode($methods->getParams($method))));
                }
        }

}
