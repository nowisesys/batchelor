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

use UUP\Site\Utility\Security\Session;

/**
 * The user object.
 * 
 * This class represent a user context with logon session.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class User
{

        /**
         * The session object.
         * @var Session 
         */
        private $_session;

        /**
         * Constructor.
         * @param Session $session The session object.
         */
        public function __construct($session)
        {
                $this->_session = $session;
        }

        /**
         * Get logged on username.
         * @return string
         */
        public function __toString()
        {
                return $this->_session->user;
        }

        /**
         * Check if user is authenticated.
         * @return bool
         */
        public function isAuthenticated(): bool
        {
                return $this->_session->authenticated();
        }

        /**
         * Get session objtect.
         * @return Session
         */
        public function getSession(): Session
        {
                return $this->_session;
        }

        /**
         * Get user principal object.
         * @return Principal
         */
        public function getPrincipal(): Principal
        {
                return new Principal($this->_session->user);
        }

}
