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

use Batchelor\Logging\Format;
use Batchelor\Logging\Logger;
use Batchelor\Logging\Special\Buffer;
use Batchelor\Logging\Writer;

/**
 * The logger adapter.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
abstract class Adapter implements Logger, Writer
{

        /**
         * The message formatter.
         * @var Format 
         */
        private $_format;
        /**
         * The message threshold.
         * @var int 
         */
        private $_threshold = 10;

        /**
         * {@inheritdoc}
         */
        public function alert(string $message, array $args = array()): bool
        {
                return $this->message(LOG_ALERT, $message, $args);
        }

        /**
         * {@inheritdoc}
         */
        public function critical(string $message, array $args = array()): bool
        {
                return $this->message(LOG_CRIT, $message, $args);
        }

        /**
         * {@inheritdoc}
         */
        public function debug(string $message, array $args = array()): bool
        {
                return $this->message(LOG_DEBUG, $message, $args);
        }

        /**
         * {@inheritdoc}
         */
        public function emergency(string $message, array $args = array()): bool
        {
                return $this->message(LOG_EMERG, $message, $args);
        }

        /**
         * {@inheritdoc}
         */
        public function error(string $message, array $args = array()): bool
        {
                return $this->message(LOG_ERR, $message, $args);
        }

        /**
         * {@inheritdoc}
         */
        public function info(string $message, array $args = array()): bool
        {
                return $this->message(LOG_INFO, $message, $args);
        }

        /**
         * {@inheritdoc}
         */
        public function notice(string $message, array $args = array()): bool
        {
                return $this->message(LOG_NOTICE, $message, $args);
        }

        /**
         * {@inheritdoc}
         */
        public function warning(string $message, array $args = array()): bool
        {

                return $this->message(LOG_WARNING, $message, $args);
        }

        /**
         * {@inheritdoc}
         */
        public function setFormat(Format $format)
        {
                $this->_format = $format;
        }

        /**
         * {@inheritdoc}
         */
        public function getFormat(): Format
        {
                return $this->_format;
        }

        /**
         * {@inheritdoc}
         */
        public function getBuffered(): Buffer
        {
                return new Buffer($this);
        }

        /**
         * {@inheritdoc}
         */
        public function getMessage(string $message, array $args = array()): array
        {
                return [
                        'stamp'   => microtime(true),
                        'message' => vsprintf($message, $args)
                ];
        }

        /**
         * {@inheritdoc}
         */
        public function setThreshold(int $priority)
        {
                $this->_threshold = $priority;
        }

        /**
         * {@inheritdoc}
         */
        public function message(int $priority, string $message, array $args = []): bool
        {
                if ($priority >= $this->_threshold) {
                        return false;
                } else {
                        return $this->doLogging($priority, $message, $args);
                }
        }

        /**
         * The real message logger.
         * 
         * @param int $priority One of the LOG_XXX constants.
         * @param string $message The message to log.
         * @param array $args Optional arguments for message.
         * @return bool True if message was successful written.
         */
        abstract protected function doLogging(int $priority, string $message, array $args = []): bool;
}
