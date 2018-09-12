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

namespace Batchelor\Logging\Special;

use Batchelor\Logging\Factory;
use Batchelor\Logging\Logger;
use Batchelor\Logging\Target\Adapter;
use Batchelor\Logging\Writer;

/**
 * The message writer multiplexer.
 * 
 * Provides support for logging to multiple message writers at once for a single 
 * priority message. Adding writers are either done:
 * 
 * <ul>
 * <li>Add single writer to multiple priorities.</li>
 * <li>Add multiple writers to single priority.</li>
 * </ul>
 * 
 * It also possible to initialize the list of writers at once using an array 
 * keyed by priorities:
 * 
 * <code>
 * $writers = [
 *      LOG_WARNING => [ $writer1, $writer2 ],
 *      LOG_ERR     => [ $writer1 ],
 * ]
 * $multiplexer->setWriters($writers);
 * </code>
 * 
 * Probably more intuitive is to add writer by passing an array of priorities:
 * 
 * <code>
 * $selected = [ ... ];
 * 
 * $multiplexer->addWriter($writer, $selected); // Add to selected set of priorities.
 * $multiplexer->addWriter($writer);            // Add to all priorities at once.                        
 * </code>
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Multiplexer extends Adapter implements Logger
{

        /**
         * The message writers.
         * @var array 
         */
        private $_targets = [];
        /**
         * The accepted priorities.
         * @var array 
         */
        private static $_priorities = [
                LOG_EMERG, LOG_ALERT, LOG_CRIT,
                LOG_ERR, LOG_WARNING, LOG_NOTICE, LOG_INFO,
                LOG_DEBUG
        ];

        /**
         * Constructor.
         * @param array $options The message writers.
         */
        public function __construct(array $options = [])
        {
                $this->setWriters($options);
        }

        /**
         * Set message writers.
         * @param array $options The message writers.
         */
        public function setWriters(array $options = [])
        {
                $this->_targets = $options;
        }

        /**
         * Add message writers.
         * @param array $options The message writers.
         */
        public function addWriters(array $options = [])
        {
                foreach ($options as $priority => $writers) {
                        $this->addPriority($priority, $writers);
                }
        }

        /**
         * Add writer in priority span.
         * 
         * Notice that the span is counting backwards starting with the highest
         * priority value first (debug by default) and continue to lowest priority
         * value (emergency by default).
         * 
         * For adding writer to handle all priorities except for debug, use the
         * start priority:
         * 
         * <code>
         * // 
         * // Set writer to handle importance from info and up:
         * // 
         * $multiplexer->addBetween($writer, LOG_INFO);
         * </code>
         * 
         * @param Writer $writer The message writer.
         * @param int $start The start priority.
         * @param int $end The end priority.
         */
        public function addBetween(Writer $writer, int $start = LOG_DEBUG, int $end = LOG_EMERG)
        {
                foreach (self::$_priorities as $priority) {
                        if (($priority <= $start) && ($priority >= $end)) {
                                $this->addTarget($priority, $writer);
                        }
                }
        }

        /**
         * Add single message writer.
         * 
         * If priorities argument is empty, then writer is added to all 
         * possible priorities.
         * 
         * @param Writer $writer The message writer.
         * @param array $priorities The logging priorities.
         */
        public function addWriter(Writer $writer, array $priorities = [])
        {
                if (count($priorities) == 0) {
                        $priorities = self::$_priorities;
                }
                foreach ($priorities as $priority) {
                        $this->addTarget($priority, $writer);
                }
        }

        /**
         * Set writers for priority.
         * 
         * @param int $priority The logging priority.
         * @param array $writers The message writers.
         */
        public function setPriority(int $priority, array $writers)
        {
                $this->_targets[$priority] = $writers;
        }

        /**
         * Add writers for priority.
         * 
         * @param int $priority The logging priority.
         * @param array $writers The message writers.
         */
        public function addPriority(int $priority, array $writers)
        {
                $this->_targets[$priority] = array_merge(
                    $this->_targets[$priority], $writers
                );
        }

        /**
         * Get all priorities.
         * @return array
         */
        public function getPriorites(): array
        {
                return array_keys($this->_targets);
        }

        /**
         * Check if proority is defined.
         * 
         * @param int $priority The logging priority.
         * @return bool
         */
        public function hasPriority(int $priority): bool
        {
                return array_key_exists($priority, $this->_targets);
        }

        /**
         * Get writers for priority.
         * 
         * @param int $priority The message priority.
         * @return array
         */
        public function getWriters(int $priority): array
        {
                if (isset($this->_targets[$priority])) {
                        return $this->_targets[$priority];
                }
        }

        /**
         * Check if writers exists.
         * 
         * @param int $priority The message priority.
         * @return boolean
         */
        public function hasWriters(int $priority): bool
        {
                if (isset($this->_targets[$priority])) {
                        return count($this->_targets[$priority]) != 0;
                } else {
                        return false;
                }
        }

        /**
         * Add target writer.
         * 
         * @param int $priority The message priority.
         * @param Writer $writer The message writer.
         */
        private function addTarget(int $priority, Writer $writer)
        {
                if (!isset($this->_targets[$priority])) {
                        $this->_targets[$priority] = [$writer];
                } else {
                        $this->_targets[$priority][] = $writer;
                }
        }

        /**
         * {@inheritdoc}
         */
        public function message(int $priority, string $message, array $args = array()): bool
        {
                if (($targets = $this->getWriters($priority))) {
                        $status = true;
                        foreach ($targets as $target) {
                                if (!($target->message($priority, $message, $args))) {
                                        $status = false;
                                }
                        }
                        return $status;
                }

                return false;
        }

        /**
         * The multiplexer logger factory function.
         * 
         * @param array $options The logger options.
         * @return Writer
         */
        public static function create(array $options) : Writer
        {
                $logger = new Multiplexer();

                foreach ($options as $type => $data) {
                        if (($data = self::addDefaults($data))) {
                                $writer = Factory::getLogger($type, $data['options']);
                                $logger->addBetween($writer, $data['priority']['start'], $data['priority']['end']);
                        }
                }

                return $logger;
        }

        /**
         * Add default settings.
         * @param array $data The incoming settings.
         * @return boolean|array
         */
        public static function addDefaults(array $data)
        {
                if (empty($data)) {
                        return false;
                } else {
                        return array_replace_recursive(
                            self::getDefaults(), $data
                        );
                }
        }

        /**
         * Get default settings.
         * @return array
         */
        public static function getDefaults(): array
        {
                return [
                        'options'  => [],
                        'priority' => [
                                'start' => LOG_DEBUG,
                                'end'   => LOG_EMERG
                        ]
                ];
        }

}
