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

namespace Batchelor\System\Service;

/**
 * The host ID.
 * 
 * When invoking methods on a web service that service need to know which job 
 * queue to operate on. The hostid value can be supplied by the remote peer thru
 * request parameter, session cookies or HTTP headers.
 * 
 * The cookie or request parameter will be named hostid. X-Batchelor-Hostid will
 * be used for HTTP header. The order for detecting the hostid will be:
 * 
 *      1. Constructor argument.
 *      2. Request parameter.
 *      3. HTTP header.
 *      4. Session cookie.
 * 
 * If none of the sources above supplie the hostid value, then the hostid gets
 * computed based on remote address.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Hostid
{

        /**
         * The HTTP header used by remote peer.
         */
        const REMOTE_HTTP_HEADER_HOSTID = 'X-Batchelor-Hostid';
        /**
         * The HTTP header provided by web server.
         */
        const SERVER_HTTP_HEADER_HOSTID = 'HTTP_X_BATCHELOR_HOSTID';

        /**
         * The hostid value.
         * @var string 
         */
        private $_value;

        /**
         * Constructor.
         * @param string $value The host ID.
         */
        public function __construct($value = null)
        {
                if (isset($value)) {
                        $this->_value = $value;
                } elseif (filter_has_var(INPUT_REQUEST, 'hostid')) {
                        $this->_value = filter_input(INPUT_REQUEST, 'hostid');
                } elseif (filter_has_var(INPUT_SERVER, 'HTTP_X_BATCHELOR_HOSTID')) {
                        $this->_value = filter_input(INPUT_SERVER, 'HTTP_X_BATCHELOR_HOSTID');
                } elseif (filter_has_var(INPUT_COOKIE, 'hostid')) {
                        $this->_value = filter_input(INPUT_COOKIE, 'hostid');
                } elseif (filter_has_var(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR')) {
                        $this->_value = md5(filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR'));
                } else {
                        $this->_value = md5(filter_input(INPUT_SERVER, 'REMOTE_ADDR'));
                }
        }

        /**
         * Get hostid value.
         * @return string
         */
        public function getValue()
        {
                return $this->_value;
        }

        /**
         * Set hostid value.
         * @param string $value The hostid value.
         */
        public function setValue($value)
        {
                $this->_value = $value;
        }

        /**
         * Set hostid value from queue name.
         * 
         * Note that calling this method will update the session cookie. It can
         * only be called before any output has been generated.
         * 
         * @param string $name The queue name.
         */
        public function setQueue($name)
        {
                if (setcookie("hostid", $this->_value, 0)) {
                        $this->_value = md5($name);
                }
        }

}
