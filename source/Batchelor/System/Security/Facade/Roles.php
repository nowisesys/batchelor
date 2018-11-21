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

namespace Batchelor\System\Security\Facade;

/**
 * The interface for roles providers.
 * 
 * Method canEnumerate() tells if a list of users can be fetched. For a roles
 * object interfacing a database or file service (possibly web) this is mostly
 * true, while for LDAP it's likely false.
 * 
 * Concrete classes implementing this interface can i.e. be an frontend against 
 * a cache service backend.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
interface Roles
{

        /**
         * Return true if users can be enumerated.
         * @return bool 
         */
        function canEnumerate(): bool;

        /**
         * Get all users having role.
         * 
         * @param string $role The requested role.
         * @return array 
         */
        function getUsers(string $role): array;

        /**
         * Return true if user has role.
         * 
         * @param string $role The requested role.
         * @param string $user The username to check.
         * @return bool 
         */
        function hasRole(string $role, string $user): bool;
}
