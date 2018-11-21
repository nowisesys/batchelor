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
 * The standard JSON web service.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
abstract class JsonService extends WebService
{

        use JsonInput;

        /**
         * Constructor.
         */
        public function __construct()
        {
                $this->setInput();

                header('Content-Type: application/json');
                parent::__construct();
        }

        /**
         * Get JSON encode options.
         * @return int
         */
        private function getEncodeOptions(): int
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
                    ), $this->getEncodeOptions()
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
                    ), $this->getEncodeOptions()
                );
        }

        /**
         * Process service request.
         * @return array
         */
        abstract protected function onRendering();
}
