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

namespace Batchelor\WebService;

use Batchelor\Controller\Standard\StandardWebService;
use Batchelor\WebService\Handler\SoapServiceHandler;
use UUP\WebService\Soap\SoapService;

/**
 * The SOAP service.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class SoapServiceFrontend extends StandardWebService
{

        /**
         * The SOAP service location.
         * @var string 
         */
        private $_location;

        /**
         * Set SOAP service location.
         * @param string $location The SOAP service location.
         */
        public function setLocation($location)
        {
                $this->_location = $location;
        }

        /**
         * {@inheritdoc}
         */
        public function render()
        {
                $service = new SoapService(SoapServiceHandler::class);
                $service->setLocation($this->_location);
                $service->setNamespace("http://it.bmc.uu.se/schemas/batchelor/soap?ver=2.0");
                
                $service->useRequest();
                $service->useWrapper();

                $description = $service->getServiceDescription();
                $description->setServiceName("Batchelor");
                $description->addClassPath("Batchelor\WebService\Types");
                
                $service->handleRequest();
        }

}
