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

namespace Batchelor\System;

use Batchelor\System\Service\Cache;
use Batchelor\System\Service\Config;
use Batchelor\System\Service\Hostid;
use Batchelor\System\Service\Logging;
use Batchelor\System\Service\Persistance;
use Batchelor\System\Service\Processor;
use Batchelor\System\Service\Queue;
use Batchelor\System\Service\Security;
use Batchelor\System\Service\Storage;

/**
 * The system component class.
 * 
 * Provides dependency injection of services into inheriting classes. By default 
 * the shared services registry is used as injector, but can be overridden by
 * passing an argument to constructor or using setInjector().
 * 
 * Requested services will be automatic injected as a public property in the
 * calling class if undefined. Next access using the service name should use the
 * service  from the public property, thus by-passing the magic get method inside 
 * the component class.
 * 
 * If dynamic upgrading the service injector after any service has been injected,
 * remember to unset any already injected service properties:
 * 
 * <code>
 * $injector = $this->getInjector();
 * $services = $injector->getServices();
 * 
 * foreach (array_keys($services) as $service) {
 *      if (property_exists($this, $service)) {
 *              unset($this->{$service});
 *      }
 * }
 * </code>
 *
 * @property-read Hostid $hostid The host ID service.
 * @property-read Persistance $persistance The data persistance service.
 * @property-read Config $app The application config.
 * @property-read Storage $data The data storage directory.
 * @property-read Cache $cache The cache service.
 * @property-read Logging $logger The logging service.
 * @property-read Queue $queue The queue service.
 * @property-read Security $security The security context service.
 * @property-read Processor $processor The task processor service.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Component
{

        /**
         * The services injector.
         * @var Services 
         */
        private $_services;

        /**
         * Constructor.
         * @param Services $services The services injector
         */
        public function __construct($services = null)
        {
                $this->_services = $services;
        }

        public function __get($name)
        {
                return $this->$name = $this->getService($name);
        }

        /**
         * Set service injector.
         * @param Services $services The services injector
         */
        public function setInjector($services)
        {
                $this->_services = $services;
        }

        /**
         * Get service injector.
         * @return Services
         */
        public function getInjector()
        {
                if (isset($this->_services)) {
                        return $this->_services;
                } else {
                        return Services::getInstance();
                }
        }

        /**
         * Get named service.
         * 
         * The difference between using this method and magic get is that the
         * requested service is not injected into this object.
         * 
         * @param string $name The service name.
         * @return object
         */
        public function getService(string $name)
        {
                if (!($services = $this->getInjector())) {
                        return null;
                }
                if ($services->hasService($name)) {
                        return $services->getService($name);
                }
        }

}
