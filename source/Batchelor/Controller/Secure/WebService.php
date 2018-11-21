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

namespace Batchelor\Controller\Secure;

use Batchelor\System\Security\Access;
use Batchelor\System\Security\Provider as SecurityProvider;
use Batchelor\System\Security\User;
use Batchelor\System\Service\Security;
use Batchelor\Web\Request\Options as RequestOptions;
use UUP\Site\Page\Service\SecureService;
use UUP\Site\Request\Params;

/**
 * The web service controller.
 * 
 * Base class for response encoders (like JSON services). Initializes the 
 * security service by defining it in service manager. Provides property
 * bag for web services.
 * 
 * This class enforce the inheriting class to implement some lifecycle methods
 * that's called in this order (with recommended logic):
 * 
 * <ul>
 * <li>onAuthorize(access, user)  -> Perform access control
 * <li>onInitialize(params)       -> Process request parameters
 * <li>onValidation(params)       -> Validate required parameters
 * <li>onRendering()              -> Process service request
 * </ul>
 * 
 * The onException(exception) method gets called when an exception is trapped
 * and should return some error to peer in a service dependent format, which
 * could be i.e. a SOAP fault.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
abstract class WebService extends SecureService
{

        use SecurityProvider;
        use RequestOptions;

        /**
         * {@inheritdoc}
         */
        public function __construct()
        {
                parent::__construct();
                $this->setSecurity();

                $this->doAuthorize($this->getSecurity());

                $this->onInitialize($this->params);
                $this->onValidation($this->params);
        }

        /**
         * Perform authorization.
         * @param Security $security The security context.
         */
        private function doAuthorize(Security $security)
        {
                $this->onAuthorize(
                    $security->getAccess(), $security->getUser()
                );
        }

        /**
         * Process request parameters.
         * @param Params $params The request parameters.
         */
        abstract protected function onInitialize(Params $params);

        /**
         * Validate required parameters.
         * @param Params $params The request parameters.
         */
        abstract protected function onValidation(Params $params);

        /**
         * Perform access control.
         * 
         * @param Access $access The access control list (ACL) object.
         * @param User $user The user context.
         */
        abstract protected function onAuthorize(Access $access, User $user);
}
