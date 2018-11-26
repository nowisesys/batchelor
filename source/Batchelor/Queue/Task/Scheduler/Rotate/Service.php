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
 * Rotate job queue service.
 * 
 * Similare to the rotate command on UNIX. This class provides rotation of
 * queues by shifting off a limited number of entries. The rotation settings
 * are read from application config unless config is provided.
 * 
 * <code>
 * $rotate = new Rotate();
 * 
 * if ($rotate->needRotation('finished', count($queue1))) {
 *      $queue1 = $rotate->getRotated('finished', $queue1);  // Rotate finished queue
 * }
 * if ($rotate->needRotation($hostid, count($queue1))) {
 *      $queue2 = $rotate->getRotated($hostid, $queue2);     // Rotate user jobs queue
 * }
 * </code>
 * 
 * This class is suitable for use as a service component.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Service extends Component
{

        /**
         * The rotate config.
         * @var array 
         */
        private $_rotate;

        /**
         * Constructor.
         */
        public function __construct(array $config = [])
        {
                if (empty($config)) {
                        $this->setConfig();
                } else {
                        $this->_rotate = $config;
                }
        }

        /**
         * Get rotation limit.
         * 
         * @param string $queue The queue name.
         * @return int
         */
        public function getLimit(string $queue): int
        {
                $queue = self::getNormalized($queue);

                if ($this->hasConfig($queue)) {
                        return $this->_rotate[$queue]['limit'];
                } else {
                        return -1;
                }
        }

        /**
         * Get number of free slots.
         * 
         * @param string $queue The queue name.
         * @return int
         */
        public function getSpare(string $queue): int
        {
                $queue = self::getNormalized($queue);

                if ($this->hasConfig($queue)) {
                        return $this->_rotate[$queue]['spare'];
                } else {
                        return 0;
                }
        }

        /**
         * Can queue be rotated?
         * 
         * @param string $queue The queue name.
         * @return bool
         */
        public function hasConfig(string $queue): bool
        {
                $queue = self::getNormalized($queue);
                return isset($this->_rotate[$queue]['limit']);
        }

        /**
         * Do queue need to be rotated?
         * 
         * @param string $queue The queue name.
         * @param int $count The queue size.
         * @return bool
         */
        public function needRotation(string $queue, int $count): bool
        {
                $queue = self::getNormalized($queue);

                if (!$this->hasConfig($queue)) {
                        return false;
                } elseif ($count <= $this->getLimit($queue)) {
                        return false;
                } else {
                        return true;
                }
        }

        /**
         * Get rotated queue.
         * 
         * @param string $queue The queue name.
         * @param array $data The queue data.
         * @return array
         */
        public function getRotated(string $queue, array $data): array
        {
                if ($this->needRotation($queue, count($data))) {
                        array_splice(
                            $data, 0, $this->getDiscard($queue, count($data))
                        );
                }

                return $data;
        }

        /**
         * Get number of items to remove.
         * 
         * @param string $queue The queue name.
         * @param int $size The current queue size.
         * @return int
         */
        private function getDiscard(string $queue, int $size): int
        {
                return $size - $this->getLimit($queue) + $this->getSpare($queue);
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
         */
        private function setConfig()
        {
                $rotate = [];

                if (!($this->app->rotate)) {
                        $rotate = [];
                } else {
                        $rotate = Config::toArray($this->app->rotate);
                }

                foreach ($rotate as $name => $conf) {
                        if (is_bool($conf) && $conf == true) {
                                $rotate[$name] = [
                                        'limit' => 50,
                                        'spare' => 25
                                ];
                        } elseif (!isset($conf['spare'])) {
                                $rotate[$name]['spare'] = $conf['limit'] / 2;
                        }
                }

                $this->_rotate = $rotate;
        }

}
