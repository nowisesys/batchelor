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
use RuntimeException;

/**
 * The stream logger.
 * 
 * This class is useful with stream wrappers or in daemon mode. When used in
 * daemon mode, it can be used for uniform logging to console and when forking
 * set logger to i.e. file.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Stream extends Adapter implements Logger
{

        /**
         * The output stream.
         * @var resource 
         */
        private $_stream;
        /**
         * The logger identity.
         * @var string 
         */
        private $_ident;
        /**
         * Flush on write.
         * @var bool 
         */
        private $_flush;
        /**
         * The logging options.
         * @var int
         */
        private $_options;

        /**
         * Constructor.
         * 
         * @param resource $stream The output stream.
         * @param string $ident The string ident is added to each message.
         * @param bool $flush Flush stream after each write.
         */
        public function __construct($stream, string $ident = "", bool $flush = false)
        {
                $this->_stream = $stream;
                $this->_ident = $ident;
                $this->_flush = $flush;

                parent::setFormat(new Standard());
        }

        /**
         * {@inheritdoc}
         */
        protected function doLogging(int $priority, string $message, array $args = array()): bool
        {
                if (($result = $this->getFormatted(
                    $priority, vsprintf($message, $args)
                    ))) {
                        $this->logMessage($result, $this->_stream);
                        return true;
                } else {
                        return false;
                }
        }

        /**
         * Write message to logfile.
         * 
         * @param string $message The log message.
         * @param string $filename The target filename.
         * @throws RuntimeException
         */
        private function logMessage(string $message, $handle)
        {
                try {
                        if (!fwrite($handle, $message . "\n") < 0) {
                                throw new RuntimeException("Failed write log message to stream");
                        }
                        if ($this->_flush && !fflush($handle)) {
                                throw new RuntimeException("Failed flush log message to stream");
                        }
                        if ($this->_options & LOG_PERROR) {
                                trigger_error($message, E_USER_NOTICE);
                        }
                } catch (RuntimeException $exception) {
                        if ($this->_options & LOG_CONS) {
                                trigger_error($message, E_USER_WARNING);
                        }
                        throw $exception;       // Re-throw
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
