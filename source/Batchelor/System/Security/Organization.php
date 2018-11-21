<?php

/*
 * Copyright (C) 2018 Anders Lövgren (Nowise Systems).
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Batchelor\System\Security;

use Batchelor\System\Component;
use Batchelor\System\Service\Config;

/**
 * The organization class.
 * 
 * An organization is defined by the domain name. The domain name is used as
 * the key for query config or LDAP for information. Currently we only support
 * using config files.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Organization extends Component
{

        /**
         * The domain name.
         * @var string 
         */
        private $_domain;

        /**
         * Constructor.
         * @param string $domain The 
         */
        public function __construct(string $domain)
        {
                $this->_domain = $domain;
        }
        
        /**
         * Get organization name.
         * @return string 
         */
        public function getName(): string
        {
                if (!$this->hasOrganizations()) {
                        return "";
                }

                $domain = $this->_domain;
                $config = $this->getOrganizations();

                if (isset($config[$domain])) {
                        return $config[$domain];
                } else {
                        return "";
                }
        }

        /**
         * Get all organizations from application config.
         * @return array
         */
        private function getOrganizations(): array
        {
                return $this->getConfig();
        }

        /**
         * Check if organizations exists.
         * @return bool
         */
        private function hasOrganizations(): bool
        {
                return !empty($this->getConfig());
        }

        /**
         * Get application config.
         * @return array 
         */
        private function getConfig(): array
        {
                if (($config = $this->getService("app"))) {
                        if (!isset($config->domains->mapping)) {
                                return [];
                        } elseif (is_object($config->domains->mapping)) {
                                return Config::toArray($config->domains->mapping);
                        } else {
                                return $config->domains->mapping;
                        }
                }
        }

}
