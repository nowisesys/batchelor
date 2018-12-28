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

use Batchelor\Queue\Remote\RemoteQueue;
use Batchelor\Queue\System\SystemQueue;
use Batchelor\Queue\WorkQueue;
use InvalidArgumentException;
use LogicException;

/**
 * The queue provider.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class QueueInitiator
{

        /**
         * The queues.
         * @var array 
         */
        private $_queues;
        /**
         * The maximum weight;
         * @var int 
         */
        private $_max = 0;

        /**
         * Constructor.
         * @param array $options The queue options.
         */
        public function __construct(array $options = [])
        {
                $this->setOptions($options);
        }

        /**
         * Check if queue is defined.
         * @param string $name The queue name.
         * @return bool
         */
        public function hasQueue(string $name): bool
        {
                return isset($this->_queues[$name]);
        }

        /**
         * Get queue data.
         * 
         * @param string $name The queue name.
         * @return array
         */
        public function getQueue(string $name): array
        {
                return $this->_queues[$name];
        }

        /**
         * Get queue object.
         * @param string $name The queue name.
         * @return WorkQueue
         */
        public function useQueue(string $name): WorkQueue
        {
                if (!$this->hasQueue($name)) {
                        throw new InvalidArgumentException("Unknown queue name $name");
                } elseif (!($data = $this->getQueue($name))) {
                        throw new InvalidArgumentException("Failed get config for queue $name");
                } elseif ($data['type'] == 'system') {
                        return new SystemQueue($data);
                } elseif ($data['type'] == 'remote') {
                        return new RemoteQueue($data);
                } else {
                        throw new InvalidArgumentException(sprintf(
                            "Unknown queue type %s", $data['type']
                        ));
                }
        }

        /**
         * Select random queue name.
         * 
         * Call this method to select one queue randomly from the available 
         * queues. Takes the queue weight into account by making queues with 
         * high weight more likely to be selected.
         *  
         * @return string The queue name.
         */
        public function selectRandomQueue(): string
        {
                $queues = array_keys($this->_queues);
                $values = array_values($this->_queues);

                if (count($queues) == 0) {
                        throw new LogicException("No work queues exists. Check the application configuration to make sure at lease one local or remote queue is defined.");
                }

                while (true) {
                        $index = rand(0, count($queues) - 1);
                        $value = rand(0, $this->_max);

                        $queue = $values[$index];

                        if ($queue['weight'] <= $value) {
                                return $queues[$index];
                        }
                }
        }

        /**
         * Set options data.
         * @param array $options The queue options.
         */
        private function setOptions(array $options)
        {
                if (empty($options)) {
                        $options = ['local' => ['type' => 'system']];
                }

                foreach ($options as $name => $data) {
                        $options[$name] = $this->addMissing(
                            $data, count($options)
                        );
                }

                $this->_queues = $options;
        }

        /**
         * Set default values in data.
         * 
         * @param array $data The queue data.
         * @param int $total The total number of queues.
         * @return array
         */
        private function addMissing(array $data, int $total): array
        {
                if (!isset($data['weight'])) {
                        $data['weight'] = $total;
                }

                if (!isset($data['type'])) {
                        if (isset($data['url'])) {
                                $data['type'] = 'remote';
                        } else {
                                $data['type'] = 'local';
                        }
                }

                if ($data['weight'] > $this->_max) {
                        $this->_max = $data['weight'];
                }

                return $data;
        }

}
