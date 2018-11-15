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

use Batchelor\Web\Upload;
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

        /**
         * Get JSON encode options.
         * @return int
         */
        private function getOptions(): int
        {
                $options = 0;

                if ($this->params->getParam("pretty") == 1) {
                        $options |= JSON_PRETTY_PRINT;
                }
                if ($this->params->getParam("escape") == 0) {
                        $options |= JSON_UNESCAPED_SLASHES;
                }
                if ($this->params->getParam("unicode") == 0) {
                        $options |= JSON_UNESCAPED_UNICODE;
                }
                if ($this->params->getParam("numeric") == 1) {
                        $options |= JSON_NUMERIC_CHECK;
                }
                if ($this->params->getParam("fraction") == 1) {
                        $options |= JSON_PRESERVE_ZERO_FRACTION;
                }

                return $options;
        }

        /**
         * {@inheritdoc}
         */
        public function onException($exception)
        {
                echo json_encode(array(
                        'status'  => 'failure',
                        'message' => $exception->getMessage(),
                        'code'    => $exception->getCode()
                    ), $this->getOptions()
                );
        }

        /**
         * {@inheritdoc}
         */
        public function render()
        {
                echo json_encode(array(
                        'status' => 'success',
                        'result' => $this->onRendering()
                    ), $this->getOptions()
                );
        }

        /**
         * Process service request.
         * 
         * Decodes input data and invoke the JSON service handler. Returns the
         * raw response from the service handler.
         * 
         * @return array
         */
        private function onRendering()
        {
                // 
                // Get method to invoke from request parameters and decode JSON
                // payload from input stream:
                // 
                $func = $this->params->getParam("func");
                $data = json_decode(file_get_contents("php://input"), true);

                // 
                // The enqueue method accepts form post:
                // 
                if ($func == "enqueue" && $data == false) {
                        $data = $this->getUpload();
                }

                // 
                // Patch for missing data (i.e. version method):
                // 
                if (empty($data)) {
                        $data = [];
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
