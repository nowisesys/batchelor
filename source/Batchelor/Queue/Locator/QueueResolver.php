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

namespace Batchelor\Queue\Locator;

use Batchelor\Cache\Config;
use Batchelor\Cache\Frontend;
use RuntimeException;

/**
 * The queue usage mapper.
 * 
 * Resolves mapping between hostid (the work queue name) and peer addresses. The 
 * jobs are kept under a common directory identified by the hostid (an hash of the
 * queue name defined by peer).
 * 
 * This class answers to questions like: 
 * 
 * <ul>
 * <li>Who is using this work queue?</li>
 * <li>Which work queues are this peer using?</li>
 * </ul>
 * 
 * An single work queue (identified by the hostid) can be shared among many 
 * users. On the same time, a peer can have multiple work queues. In some sense,
 * we have a many to many relation to deal with.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class QueueResolver extends Frontend
{

        /**
         * Constructor.
         */
        public function __construct()
        {
                $options = $this->getConfig();
                parent::__construct($options['type'], $options['options']);
        }

        /**
         * Add mapping for hostid.
         * 
         * The address is optional, but an runtime exception will be throwed if
         * remote address is missing. By default the remote address is taken from
         * calling peer.
         * 
         * The mapping in hostid/inaddr index is only added if missing. The same
         * for the hostid and address lists. In other words, calling this method 
         * twice with same arguments is a noop.
         * 
         * @param string $hostid The hostid string.
         * @param string $address The peer address.
         */
        public function addMapping(string $hostid, string $address = null)
        {
                if (!isset($address)) {
                        $address = $this->getRemote();
                }

                if (!$this->exists($hostid)) {
                        $this->addIndex($hostid, $address);
                        $this->addIndex("hostid", $hostid);
                } else {
                        $this->addIndex($hostid, $address);
                }

                if (!$this->exists($address)) {
                        $this->addIndex($address, $hostid);
                        $this->addIndex("inaddr", $address);
                } else {
                        $this->addIndex($address, $hostid);
                }
        }

        /**
         * Get peer addresses using work queue.
         * 
         * @param string $hostid The hostid string.
         * @return array 
         */
        public function getPeers(string $hostid): array
        {
                return $this->read($hostid);
        }

        /**
         * Get work queues used by address.
         * 
         * The address is optional, but an runtime exception will be throwed if
         * remote address is missing. By default the remote address is taken from
         * calling peer.
         * 
         * @param string $address The remote address.
         */
        public function getQueues(string $address = null): array
        {
                if (!isset($address)) {
                        $address = $this->getRemote();
                }

                return $this->read($address);
        }

        /**
         * Get all peer addresses.
         * @return array
         */
        public function getPeersAll(): array
        {
                return $this->getIndex("inaddr");
        }

        /**
         * Get all qeuue names.
         * @return array
         */
        public function getQueuesAll(): array
        {
                return $this->getIndex("hostid");
        }

        /**
         * Get remote address.
         * 
         * @return string
         * @throws RuntimeException
         */
        private function getRemote(): string
        {
                if (!($address = filter_input(INPUT_SERVER, 'REMOTE_ADDR'))) {
                        throw new RuntimeException("The remote address is not known");
                } else {
                        return $address;
                }
        }

        /**
         * Add address to index.
         * @param string $value The remote address.
         */
        private function addAddress(string $value)
        {
                $this->addIndex("inaddr", $value);
        }

        /**
         * Add hostid to index.
         * @param string $value The queue hostid.
         */
        private function addQueue(string $value)
        {
                $this->addIndex("hostid", $value);
        }

        /**
         * Add value to index.
         * 
         * @param string $key The cache key.
         * @param string $value The cache value.
         */
        private function addIndex(string $key, string $value)
        {
                if (!($values = $this->read($key))) {
                        $values = [];
                }

                if (!in_array($value, $values)) {
                        $values[] = $value;
                        $this->save($key, $values);
                }
        }

        /**
         * Get index data.
         * 
         * @param string $key The cache key.
         * @return array
         */
        private function getIndex(string $key): array
        {
                if ($this->exists($key)) {
                        return $this->read($key);
                } else {
                        return [];
                }
        }

        /**
         * Get cache config.
         * @return array
         */
        private function getConfig(): array
        {
                return (new Config('resolver', 'persist'))->getOptions();
        }

}
