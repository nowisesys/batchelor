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

use Batchelor\WebService\Client\SoapClientHandler;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The SOAP client command.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class SoapCommand extends BaseCommand
{

        protected function configure()
        {
                parent::configure();

                $this->setName("soap");
                $this->setDescription("SOAP service client");

                $this->addOption("no-cache", null, InputOption::VALUE_NONE, "Disable WSDL document cache");
                $this->addOption("no-throw", null, InputOption::VALUE_NONE, "Disable SOAP fault exception");
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
                parent::execute($input, $output);

                $client = new SoapClientHandler();
                $client->setBase($input->getOption('base'));

                $func = $input->getOption('func');
                $data = $input->getOption('params');

                if ($input->getOption("trace")) {
                        $client->setTracing();
                }
                if ($input->getOption("no-cache")) {
                        $client->setOption("cache_wsdl", WSDL_CACHE_NONE);
                }
                if ($input->getOption("no-throw")) {
                        $client->setOption("exceptions", false);
                }

                if ($data) {
                        $response = $client->callMethod($func, json_decode($data, true));
                } else {
                        $response = $client->callMethod($func);
                }

                if ($input->getOption("trace")) {
                        $this->showTrace($client->getTracing(), $output);
                }

                if ($response) {
                        $this->showResult((array) $response, $output, $input->getOption("decode"));
                }
        }

}
