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

namespace Batchelor\Logging\Target;

use Batchelor\Logging\Format\Standard;
use Batchelor\Logging\Logger;

/**
 * The callback logger.
 * 
 * Invokes the callable for each log message passing the priority for filtering
 * in callable. The message is formatted using currently set format object if
 * the $decorate argument for constructor is true.
 * 
 * <code>
 * $logger = new Callback(static function(int $priority, string $message) {
 *      printf("%d: %s\n", $priority, $message);
 * });
 * $logger->info("hello world!");
 * </code>
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Callback extends Adapter implements Logger
{

        /**
         * The logger identity.
         * @var string 
         */
        private $_ident;
        /**
         * The message callback.
         * @var callback 
         */
        private $_callback;
        /**
         * Decorate messages using formatter.
         * @var bool 
         */
        private $_decorate;

        /**
         * Constructor.
         * 
         * @param callable $callback The message callback.
         * @param bool $decorate Use current formatter object.
         */
        public function __construct(callable $callback, bool $decorate = false, string $ident = "")
        {
                $this->_ident = $ident;

                $this->_callback = $callback;
                $this->_decorate = $decorate;

                parent::setFormat(new Standard());
        }

        /**
         * {@inheritdoc}
         */
        protected function doLogging(int $priority, string $message, array $args = array()): bool
        {
                if (!($callback = $this->_callback)) {
                        return false;
                } elseif ($this->_decorate) {
                        $callback($priority, $this->getFormatted($priority, vsprintf($message, $args)));
                        return true;
                } else {
                        $callback($priority, vsprintf($message, $args));
                        return true;
                }
        }

        /**
         * Get formatted message.
         * 
         * @param int $priority One of the LOG_XXX constants.
         * @param string $message The message to log.
         */
        private function getFormatted(int $priority, string $message): string
        {
                if (($format = parent::getFormat())) {
                        return $format->getMessage([
                                    'stamp'    => time(),
                                    'ident'    => $this->_ident,
                                    'pid'      => $this->getProcess(),
                                    'priority' => $priority,
                                    'message'  => trim($message)
                        ]);
                }
        }

        /**
         * Get process ID.
         * @return int
         */
        private function getProcess(): int
        {
                return getmypid();
        }

}
