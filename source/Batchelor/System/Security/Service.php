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
 * The security service.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Service
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
        public function __construct(Session $session)
        {
                $this->_session = $session;
        }

        /**
         * Get user object.
         * 
         * The returned object represent the logged on user, possibly 
         * authenticated, but not required.
         * 
         * @return User
         */
        public function getUser(): User
        {
                return new User($this->_session);
        }

        /**
         * Get access control list (ACL) object.
         * @return Access
         */
        public function getAccess(): Access
        {
                return new Access();
        }

}
