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
use Batchelor\System\Security\Facade\Realms;

/**
 * The user principal object.
 *
 * This class represent the user principal of a logged in user. The user 
 * principal can be looked up by setting the domains->default config setting
 * to either a callable or a realms facade.
 * 
 * When user is authenticated against a federated logon service then the usernme
 * is always a real principal name (user@realm), in which case the domain part
 * lookup is never used.
 * 
 * @author Anders Lövgren (Nowise Systems)
 * @see Realms
 */
class Principal extends Component
{

        /**
         * Default user domain.
         */
        const DEFAULT_DOMAIN = 'local';

        /**
         * The user principal name.
         * @var string 
         */
        private $_principal;
        /**
         * The username.
         * @var string 
         */
        private $_user;
        /**
         * The domain name part.
         * @var string 
         */
        private $_from;

        /**
         * Constructor.
         * @param string $user The username.
         */
        public function __construct(string $user)
        {
                list($user, $from) = $this->getComponents($user);

                $this->_user = $user;
                $this->_from = $from;

                $this->_principal = sprintf("%s@%s", $user, $from);
        }

        /**
         * Get username.
         * @return string
         */
        public function getUsername(): string
        {
                return $this->_user;
        }

        /**
         * Get user domain.
         * @return string
         */
        public function getDomain(): string
        {
                return $this->_from;
        }

        /**
         * Get user principal name.
         * @return string
         */
        public function getPrincipalName(): string
        {
                return $this->_principal;
        }

        /**
         * Get organization name.
         * @return Organization 
         */
        public function getOrganization(): Organization
        {
                return new Organization($this->_from);
        }

        /**
         * Get default user domain.
         * @return string
         */
        private function getDefaultDomain(): string
        {
                if (!($config = $this->getConfig())) {
                        return self::DEFAULT_DOMAIN;
                } elseif (is_string($config)) {
                        return $config;
                } elseif (is_callable($config)) {
                        return $config($this->_user);
                } elseif ($config instanceof Realms) {
                        return $config->getRealm($this->_user);
                }
        }

        /**
         * Get principal name components.
         * 
         * @param string $user The username.
         * @return array
         */
        private function getComponents(string $user): array
        {
                if (strstr($user, "@")) {
                        return explode("@", $user);
                } else {
                        return [
                                $user, $this->getDefaultDomain()
                        ];
                }
        }

        /**
         * Get application config.
         * @return string|callable
         */
        private function getConfig()
        {
                if (($config = $this->getService("app"))) {
                        if (!isset($config->domains->default)) {
                                return self::DEFAULT_DOMAIN;
                        } else {
                                return $config->domains->default;
                        }
                }
        }

}
