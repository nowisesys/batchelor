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

use Batchelor\Web\Request\JsonInput;

/**
 * File download service.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
abstract class SecureFileService extends StandardWebService
{

        use JsonInput;

        /**
         * Constructor.
         */
        public function __construct()
        {
                $this->setInput();
                parent::__construct();
        }

        public function render()
        {
                $this->onRendering();
        }

        public function onException($exception)
        {
                if ($exception->getCode() != 0 &&
                    $exception->getCode() != 503) {
                        http_response_code($exception->getCode());
                } else {
                        http_response_code(503);
                        echo $exception->getMessage();
                }
        }

        /**
         * Process service request.
         * @return array
         */
        abstract protected function onRendering();
}
