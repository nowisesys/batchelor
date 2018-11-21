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

namespace Batchelor\Controller\Standard;

use Batchelor\System\Security\Provider as SecurityProvider;
use Batchelor\Web\Request\Options as RequestOptions;
use UUP\Site\Page\Service\StandardService;

/**
 * The standard web service controller.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
abstract class StandardWebService extends StandardService
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
        }

        /**
         * {@inheritdoc}
         */
        public function onException($exception)
        {
                error_log(print_r($exception, true));
        }

}
