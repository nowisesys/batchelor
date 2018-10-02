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

use SoapClient;

/**
 * The web service client application.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class SoapClientHandler
{

        /**
         * The base URL.
         * @var string 
         */
        private $_base = "http://localhost/batchelor/ws/soap/";
        /**
         * The  WSDL address.
         * @var string 
         */
        private $_wsdl;
        /**
         * The SOAP client.
         * @var SoapClient 
         */
        private $_client;
        /**
         * The trace data.
         * @var array
         */
        private $_tracing;
        /**
         * The default SOAP options
         * @var array 
         */
        private $_options = [
                'soap_version' => SOAP_1_2,
                'exceptions'   => true,
                'trace'        => 0,
                'cache_wsdl'   => WSDL_CACHE_BOTH
        ];

        /**
         * Constructor.
         */
        public function __construct()
        {
                if (!extension_loaded("soap")) {
                        throw new RuntimeException("The SOAP extension is not loaded");
                }
        }

        /**
         * Set base address URL.
         * @param string $addr The base address.
         */
        public function setBase($addr)
        {
                $this->_base = $addr;
                $this->_wsdl = sprintf("%s?wsdl=1", $this->_base);
        }

        /**
         * Get SOAP client options.
         * @return array
         */
        public function getOptions()
        {
                return $this->_options;
        }

        /**
         * Set SOAP options.
         * @param array $options The SOAP client options.
         */
        public function setOptions($options)
        {
                $this->_options = $options;
        }

        /**
         * Set SOAP client option.
         * @param string $name The option name.
         * @param mixed $value The option value.
         */
        public function setOption($name, $value)
        {
                $this->_options[$name] = $value;
        }

        /**
         * Get SOAP client option.
         * @param string $name The option name.
         */
        public function getOption($name)
        {
                return $this->_options[$name];
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
                $this->setOption("trace", true);
                $this->setOption("exceptions", false);
                $this->setOption("cache_wsdl", WSDL_CACHE_NONE);
        }

        /**
         * Check if tracing is enabled.
         * @return bool
         */
        public function useTracing()
        {
                return $this->getOption("trace");
        }

        /**
         * Invoke SOAP method.
         * @param string $func The method name.
         * @param array $params Optional method parameters.
         * @return array
         */
        public function callMethod($func, $params = [])
        {
                $client = $this->getClient();
                $result = $client->__soapCall($func, array($params));

                if ($this->useTracing()) {
                        $this->_tracing = [
                                'action'   => [
                                        'method' => $func,
                                        'params' => $params
                                ],
                                'request'  => [
                                        'head' => $client->__getLastRequestHeaders(),
                                        'body' => $client->__getLastRequest()
                                ],
                                'response' => [
                                        'head' => $client->__getLastResponseHeaders(),
                                        'body' => $client->__getLastResponse()
                                ],
                                'result'   => $result
                        ];
                }

                return $result;
        }

        /**
         * Get SOAP client.
         * @return SoapClient
         */
        private function getClient()
        {
                if (isset($this->_client)) {
                        return $this->_client;
                } else {
                        return $this->_client = new SoapClient($this->_wsdl, $this->_options);
                }
        }

}
