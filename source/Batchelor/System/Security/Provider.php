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

use Batchelor\System\Service\Security;
use Batchelor\System\Services;

/**
 * The security service provider.
 * @author Anders Lövgren (Nowise Systems)
 */
trait Provider
{

        /**
         * Register the security service.
         */
        private function setSecurity()
        {
                $services = Services::getInstance();
                $services->register('security', new Security($this->session));
        }

        /**
         * Get security service.
         * @return Security
         */
        private function getSecurity(): Security
        {
                $services = Services::getInstance();
                return $services->getService('security');
        }

}
