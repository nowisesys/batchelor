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

namespace Batchelor\WebService;

use Batchelor\WebService\Common\ServiceFrontend;
use Batchelor\WebService\Handler\JsonServiceHandler;

/**
 * The JSON service.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class JsonServiceFrontend extends ServiceFrontend
{

        /**
         * Constructor.
         */
        public function __construct()
        {
                parent::__construct();
                header('Content-Type: application/json');
        }

        public function onException($exception)
        {
                echo json_encode(array(
                        'status'  => 'failure',
                        'message' => $exception->getMessage(),
                        'code'    => $exception->getCode()
                ));
        }

        public function render()
        {
                echo json_encode(array(
                        'status' => 'success',
                        'result' => $this->onRendering()
                ));
        }

        private function onRendering()
        {
                $func = $this->params->getParam("func");
                $data = json_decode(file_get_contents("php://input"), true);

                // 
                // TODO: remove error logging when JSON API is debugged.
                // 
                error_log(print_r([
                        'func' => $func,
                        'data' => $data
                        ], true));

                if (empty($data)) {
                        $data = [];
                }

                return (new JsonServiceHandler())
                        ->process($func, $data);
        }

}
