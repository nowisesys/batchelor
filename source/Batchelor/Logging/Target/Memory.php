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

use Batchelor\Logging\Format\Custom;
use Batchelor\Logging\Logger;

/**
 * The memory logger.
 * 
 * Collects formatted messages in an array. The default message formatting logs
 * datetime in ISO-format.
 * 
 * <code>
 * // 
 * // Use default message formatting:
 * // 
 * $logger = new Memory();
 * 
 * // 
 * // Apply custom formatting 
 * // 
 * $logger = new Memory([
 *      'expand'   => "@datetime@ @message@ (@ident@::@priority@)",
 *      'datetime' => DateTime::FORMAT_HUMAN
 * ]);
 * </code>
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Memory extends Adapter implements Logger
{

        /**
         * The message buffer.
         * @var array 
         */
        private $_messages = [];
        /**
         * The logger identity.
         * @var string 
         */
        private $_ident;

        /**
         * Constructor.
         * 
         * @param array $options The format options.
         * @param string $ident The string ident is added to each message.
         */
        public function __construct(array $options = [], string $ident = "")
        {
                $this->_ident = $ident;
                parent::setFormat(Custom::create($options));
        }

        /**
         * {@inheritdoc}
         */
        protected function doLogging(int $priority, string $message, array $args = array()): bool
        {
                $this->_messages[] = $this->getFormatted($priority, vsprintf($message, $args));
                return true;
        }

        /**
         * Get all messages.
         * @return array 
         */
        public function getMessages(): array
        {
                return $this->_messages;
        }

        /**
         * Check if log messages exist.
         * @return bool
         */
        public function hasMesssages(): bool
        {
                return count($this->_messages) != 0;
        }

        /**
         * Clear message buffer.
         */
        public function clear()
        {
                $this->_messages = [];
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
