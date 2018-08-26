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

use Batchelor\System\Component;
use RuntimeException;

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
 *      2. Request parameter (GET and POST).
 *      3. From custom HTTP header.
 *      4. From session cookie.
 * 
 * If none of the sources above supplie the hostid value, then the hostid gets
 * computed based on remote address.
 * 
 * <code>
 * // 
 * // Cookie will only be set if requested or explicit switching queue:
 * // 
 * $hostid = new Hostid();
 * $hostid->setQueue('my-queue');
 * </code>
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Hostid extends Component
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
         * The default queue name.
         */
        const DEFAULT_QUEUE_NAME = 'default-queue';

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
                $requested = $this->getRequested($value);
                $persisted = $this->getPersisted();

                if ($requested != $persisted) {
                        if (empty($requested)) {
                                $this->clearPersisted();
                        } else {
                                $this->setPersisted($requested);
                        }
                }
                if (empty($requested)) {
                        $requested = $this->getDefault();
                }

                $this->_value = $requested;
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
                if (empty($name)) {
                        $this->clearPersisted();
                        $this->setDefault();
                } else {
                        $this->setPersisted(md5($name));
                        $this->setValue(md5($name));
                }
        }

        /**
         * Set default hostid.
         */
        private function setDefault()
        {
                $this->_value = $this->getDefault();
        }

        /**
         * Get default hostid.
         * 
         * The default hostid is either based on remote address (possibly proxied).
         * If remote address is missing (i.e. from CLI), then default queue name is
         * used.
         * 
         * @return string
         */
        private function getDefault()
        {
                if (filter_has_var(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR')) {
                        return md5(filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR'));
                } elseif (filter_has_var(INPUT_SERVER, 'REMOTE_ADDR')) {
                        return md5(filter_input(INPUT_SERVER, 'REMOTE_ADDR'));
                } else {
                        return md5(self::DEFAULT_QUEUE_NAME);
                }
        }

        /**
         * Get requested hostid.
         * 
         * @param string $value The local value.
         * @param string $default The default value.
         * @return string
         */
        private function getRequested($value = null, $default = null)
        {
                if (isset($value)) {
                        return $value;
                } elseif (filter_has_var(INPUT_GET, 'hostid')) {
                        return filter_input(INPUT_GET, 'hostid');
                } elseif (filter_has_var(INPUT_POST, 'hostid')) {
                        return filter_input(INPUT_POST, 'hostid');
                } elseif (filter_has_var(INPUT_SERVER, 'HTTP_X_BATCHELOR_HOSTID')) {
                        return filter_input(INPUT_SERVER, 'HTTP_X_BATCHELOR_HOSTID');
                } elseif (filter_has_var(INPUT_COOKIE, 'hostid')) {
                        return filter_input(INPUT_COOKIE, 'hostid');
                } else {
                        return $default;
                }
        }

        /**
         * Get persisted hostid.
         * @return string
         */
        private function getPersisted()
        {
                if ($this->persistance->exists('hostid')) {
                        return $this->persistance->read('hostid');
                }
        }

        /**
         * Set persisted hostid.
         * @param string $value The hostid value.
         */
        private function setPersisted($value)
        {
                $this->persistance->save('hostid', $value);
        }

        /**
         * Clear persisted hostid.
         */
        private function clearPersisted()
        {
                $this->persistance->delete('hostid');
        }

}
