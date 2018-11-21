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
 * The interface for doamin providers.
 * 
 * Used for resolving realms for users. In reality this involves looking up
 * domain suffix for user principal given a sole username. The concrete class
 * is typical backed by a cache backend.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
interface Realms
{

        /**
         * Get user realm.
         * 
         * @param string $user The username.
         * @return string 
         */
        function getRealm(string $user): string;
}
