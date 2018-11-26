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

namespace Batchelor\Queue\Task\Scheduler\Rotate;

use Batchelor\System\Component;
use Batchelor\System\Service\Config;

/**
 * Rotate queue.
 * 
 * Similare to the rotate command on UNIX. This class provides rotation of
 * queues by shifting off a limited number of entries. The rotation settings
 * are read from application config.
 * 
 * <code>
 * $rotate = new Rotate('finished');
 * if ($rotate->needRotation(count($queue1))) {
 *      $queue1 = $rotate->getRotated($queue1);      // Rotate finished queue
 * }
 * 
 * $rotate = new Rotate($hostid);
 * if ($rotate->needRotation(count($queue2))) {
 *      $queue2 = $rotate->getRotated($queue2);      // Rotate user jobs queue
 * }
 * </code>
 * 
 * This class is suitable for use as an functionality extension class inside
 * another class.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Single extends Component
{

        /**
         * The rotate config.
         * @var array 
         */
        private $_rotate;

        /**
         * Constructor.
         * @param string $queue The queue name.
         */
        public function __construct(string $queue)
        {
                $this->setConfig(self::getNormalized($queue));
        }

        /**
         * Get rotation limit.
         * @return int
         */
        public function getLimit(): int
        {
                return $this->_rotate['limit'] ?: -1;
        }

        /**
         * Get number of free slots.
         * @return int
         */
        public function getSpare(): int
        {
                return $this->_rotate['spare'] ?: 0;
        }

        /**
         * Should queue be rotated?
         * @return bool
         */
        public function hasConfig(): bool
        {
                return isset($this->_rotate['limit']);
        }

        /**
         * Do queue need to be rotated?
         * 
         * @param int $count The queue size.
         * @return bool
         */
        public function needRotation(int $count): bool
        {
                if (!$this->hasConfig()) {
                        return false;
                } elseif ($count <= $this->getLimit()) {
                        return false;
                } else {
                        return true;
                }
        }

        /**
         * Get rotated queue.
         * 
         * @param array $data The queue data.
         * @return array
         */
        public function getRotated(array $data): array
        {
                if ($this->needRotation(count($data))) {
                        array_splice(
                            $data, 0, $this->getDiscard(count($data))
                        );
                }

                return $data;
        }

        /**
         * Get number of items to remove.
         * 
         * @param int $size The current queue size.
         * @return int
         */
        private function getDiscard(int $size): int
        {
                return $size - $this->getLimit() + $this->getSpare();
        }

        /**
         * Get normalized queue name.
         * 
         * Maps the user queue name to symbolic name "@hostid" that can be used
         * for looking up rotation settings.
         * 
         * @param string $queue The queue name.
         * @return string
         */
        private static function getNormalized(string $queue)
        {
                switch ($queue) {
                        case 'pending':
                        case 'running':
                        case 'finished':
                                return $queue;
                        default:
                                return "@hostid";
                }
        }

        /**
         * Set rotation config.
         * @param string $queue The queue name.
         */
        private function setConfig(string $queue)
        {
                $rotate = [];

                if (!($this->app->rotate)) {
                        $rotate = [];
                } elseif (!($this->app->rotate->offsetExists($queue))) {
                        $rotate = [];
                } else {
                        $rotate = $this->app->rotate->offsetGet($queue);
                }

                if (is_bool($rotate) && $rotate == true) {
                        $rotate = [
                                'limit' => 50,
                                'spare' => 25
                        ];
                }

                if (!isset($rotate['spare']) && isset($rotate['limit'])) {
                        $rotate['spare'] = $rotate['limit'] / 2;
                }

                $this->_rotate = $rotate;
        }

}
