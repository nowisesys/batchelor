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

use Batchelor\Controller\Standard\JsonService;
use Batchelor\Web\Upload;
use Batchelor\WebService\Handler\JsonServiceHandler;

/**
 * The JSON service.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class JsonServiceFrontend extends JsonService
{

        /**
         * Process service request.
         * 
         * Decodes input data and invoke the JSON service handler. Returns the
         * raw response from the service handler.
         * 
         * @return array
         */
        protected function onRendering()
        {
                // 
                // Get method to invoke decoded JSON payload:
                // 
                $func = $this->params->getParam("func");
                $data = $this->getInput();

                // 
                // The enqueue method accepts form post:
                // 
                if ($func == "enqueue" && $data == false) {
                        $data = $this->getUpload();
                }

                // 
                // Process service request and return result:
                // 
                return (new JsonServiceHandler())
                        ->process($func, $data);
        }

        /**
         * Get uploaded file.
         * @return array
         */
        private function getUpload(): array
        {
                return [
                        'data' => (new Upload())->getFilepath(),
                        'type' => 'file',
                        'name' => $this->params->getParam('name'),
                        'task' => $this->params->getParam('task')
                ];
        }

}
