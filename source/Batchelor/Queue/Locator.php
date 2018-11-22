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

namespace Batchelor\Queue;

use Batchelor\Queue\Locator\QueueInitiator;
use Batchelor\Queue\Locator\QueueMapper;
use Batchelor\System\Component;

/**
 * The queue locator.
 * 
 * Uses the locator mapper and initiator for controlling the hostid to queue
 * mapping. The mapper class takes care of resolving hostid to queue, while the
 * initator creates the queue object. Both classes uses the application config
 * for setup.
 * 
 * <code>
 * // 
 * // Get queue for hostid:
 * // 
 * $queue = $locator->useQueue($hostid);
 * 
 * // 
 * // Removing a queue will delete it from the mapper:
 * // 
 * $locator->removeQueue($hostid);
 * </code>
 * 
 * Notice that it's crucial that the mapping is permanent. If a hostid mapping is 
 * lost, then its not guaranteed to be restored to the same work queue if passing 
 * the same hostid for initializing once again.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Locator extends Component
{

        /**
         * The hostid to queue map.
         * @var QueueMapper 
         */
        private $_mapper;
        /**
         * The available work queues.
         * @var QueueInitiator 
         */
        private $_queues;

        /**
         * Constructor.
         * @param array $options The queue options.
         */
        public function __construct(array $options)
        {
                $this->_mapper = new QueueMapper($this->getConfig());
                $this->_queues = new QueueInitiator($options);
        }

        /**
         * Get queue for hostid string.
         * 
         * @param string $hostid The hostid string.
         * @return WorkQueue 
         */
        public function useQueue(string $hostid): WorkQueue
        {
                if ($this->_mapper->exists($hostid)) {
                        $name = $this->_mapper->read($hostid);
                        return $this->_queues->useQueue($name);
                } else {
                        $name = $this->_queues->selectRandomQueue();
                        $this->_mapper->save($hostid, $name);
                        return $this->_queues->useQueue($name);
                }
        }

        /**
         * Check if hostid has work queue.
         * 
         * @param string $hostid The hostid string.
         * @return bool
         */
        public function hasQueue(string $hostid): bool
        {
                return $this->_mapper->exists($hostid);
        }

        /**
         * Get work queue name.
         * 
         * @param string $hostid The hostid string.
         * @return string 
         */
        public function getQueue(string $hostid): string
        {
                return $this->_mapper->read($hostid);
        }

        /**
         * Remove queue mapping.
         * 
         * @param string $hostid The hostid string.
         */
        public function removeQueue(string $hostid)
        {
                $this->_mapper->delete($hostid);
        }

        /**
         * Is this hostid on local or an remote queue?
         * 
         * @param string $hostid The hostid string.
         * @return bool
         */
        public function isOnLocal(string $hostid): bool
        {
                return $this->_mapper->read($hostid) == "local";
        }

        /**
         * Get service configuration.
         * @return array 
         */
        private function getConfig(): array
        {
                if (($config = $this->getService("app"))) {
                        if (!isset($config->cache->mapper)) {
                                return ['type' => 'detect'];
                        } elseif (is_string($config->cache->mapper)) {
                                return ['type' => $config->cache->mapper];
                        } else {
                                return Config::toArray($config->cache->mapper);
                        }
                }
        }

}
