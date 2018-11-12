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

namespace Batchelor\WebService\Client;

use RuntimeException;

/**
 * The web service client application.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class JsonClientHandler
{

        /**
         * The base URL.
         * @var string 
         */
        private $_base = "http://localhost/batchelor/api/json/";
        /**
         * The cURL client.
         * @var resource 
         */
        private $_client;
        /**
         * The tracing mode.
         * @var bool 
         */
        private $_trace = false;
        /**
         * The trace data.
         * @var array
         */
        private $_tracing;

        /**
         * Constructor.
         */
        public function __construct()
        {
                if (!extension_loaded("json")) {
                        throw new RuntimeException("The JSON extension is not loaded");
                }
        }

        /**
         * Destructor.
         */
        public function __destruct()
        {
                curl_close($this->_client);
        }

        /**
         * Set base address URL.
         * @param string $addr The base address.
         */
        public function setBase($addr)
        {
                $this->_base = $addr;
        }

        /**
         * Get tracing data.
         * @return array
         */
        public function getTracing()
        {
                return $this->_tracing;
        }

        /**
         * Enable trace mode.
         */
        public function setTracing()
        {
                $this->_trace = true;
        }

        /**
         * Check if tracing is enabled.
         * @return bool
         */
        public function useTracing()
        {
                return $this->_trace;
        }

        /**
         * Invoke JSON method.
         * @param string $func The method name.
         * @param array $params Optional method parameters.
         * @return array
         */
        public function callMethod($func, $params = [])
        {
                $client = $this->getClient();
                $this->_tracing = [];

                if (!(curl_setopt($client, CURLOPT_URL, sprintf("%s/%s", $this->_base, $func)))) {
                        throw new RuntimeException(curl_error($client));
                }
                if (!(curl_setopt($client, CURLOPT_POSTFIELDS, json_encode($params)))) {
                        throw new RuntimeException(curl_error($client));
                }
                if (!($result = curl_exec($client))) {
                        throw new RuntimeException(curl_error($client));
                }

                if ($this->useTracing()) {
                        $this->_tracing = [
                                'action'  => [
                                        'method' => $func,
                                        'params' => $params
                                ],
                                'headers' => $this->_tracing,
                                'result'  => $result
                        ];
                }

                return $result;
        }

        /**
         * Get cURL client.
         * @return resource
         */
        private function getClient()
        {
                if (is_resource($this->_client)) {
                        return $this->_client;
                }

                if (!($curl = curl_init())) {
                        throw new RuntimeException("Failed initialize cURL");
                }

                if (!curl_setopt($curl, CURLOPT_RETURNTRANSFER, true)) {
                        throw new RuntimeException(curl_error($curl));
                }
                if (!curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true)) {
                        throw new RuntimeException(curl_error($curl));
                }
                if (!curl_setopt($curl, CURLOPT_HTTPHEADER, ["X-Requested-With: XMLHttpRequest", "Content-Type: application/json; charset=utf-8"])) {
                        throw new RuntimeException(curl_error($curl));
                }
                if (!curl_setopt($curl, CURLOPT_POST, true)) {
                        throw new RuntimeException(curl_error($curl));
                }
                if (!(curl_setopt($curl, CURLOPT_HEADERFUNCTION, function($curl, $header) {
                            $length = strlen($header);
                            $header = explode(':', $header, 2);

                            if (count($header) < 2) {
                                    return $length;
                            } else {
                                    $name = strtolower(trim($header[0]));
                            }

                            if (!array_key_exists($name, $this->_tracing)) {
                                    $this->_tracing[$name] = trim($header[1]);
                            } else {
                                    $previous = $this->_tracing[$name];
                                    $this->_tracing[$name] = [
                                            $previous, trim($header[1])
                                    ];
                            }

                            return $length;
                    }))) {
                        throw new RuntimeException(curl_error($curl));
                }

                return $this->_client = $curl;
        }

}
