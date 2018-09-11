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
 * The file logger.
 * 
 * <code>
 * $logger = new File("/var/log/batchelor.log");
 * $logger->setFormat(File::DATETIME_LOCALE);   // Use "%x %X" as datetime format.
 * $logger->info("hello world!");
 * </code>
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class File extends Adapter implements Logger
{

        /**
         * The target file.
         * @var string 
         */
        private $_filename;
        /**
         * The logger identity.
         * @var string 
         */
        private $_ident;
        /**
         * The logging options.
         * @var int
         */
        private $_options;

        /**
         * Constructor.
         * 
         * @param string $filename The target filename.
         * @param string $ident The string ident is added to each message.
         * @param int $options The logging options (bitmask of zero or more LOG_XXX contants).
         * @param int $granularity The logging granularity (one of the GRANULARITY_XXX constats).
         */
        public function __construct(string $filename, string $ident = "", int $options = LOG_CONS | LOG_PID)
        {
                $this->_filename = $filename;
                $this->_ident = $ident;
                $this->_options = $options;

                parent::setFormat(new Standard());
        }

        /**
         * Set logger identity.
         * @param string $ident The string ident is added to each message.
         */
        public function setIdentity(string $ident)
        {
                $this->_ident = $ident;
        }

        /**
         * Set logger options.
         * @param int $options The logging options (bitmask of zero or more LOG_XXX contants).
         */
        public function setOptions(int $options)
        {
                $this->_options = $options;
        }

        /**
         * {@inheritdoc}
         */
        public function message(int $priority, string $message, array $args = []): bool
        {
                if (($result = $this->getFormatted(
                    $priority, vsprintf($message, $args)
                    ))) {
                        $this->logMessage($result, $this->_filename);
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
        private function logMessage(string $message, string $filename)
        {
                try {
                        if (!($handle = fopen($this->_filename, "a"))) {
                                throw new RuntimeException("Failed open $filename for write");
                        }
                        if (!flock($handle, LOCK_EX)) {
                                throw new RuntimeException("Failed lock $filename");
                        }
                        if (!fwrite($handle, $message . "\n") < 0) {
                                throw new RuntimeException("Failed write log message to $filename");
                        }
                        if ($this->_options & LOG_PERROR) {
                                trigger_error($message, E_USER_NOTICE);
                        }
                } catch (RuntimeException $exception) {
                        if ($this->_options & LOG_CONS) {
                                trigger_error($message, E_USER_WARNING);
                        }
                        throw $exception;       // Re-throw
                } finally {
                        if (!is_resource($handle)) {
                                return;
                        }
                        if (!flock($handle, LOCK_UN)) {
                                throw new RuntimeException("Failed unlock $filename");
                        }
                        if (!fclose($handle)) {
                                throw new RuntimeException("Failed close $filename");
                        }
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
                if ($this->_options & LOG_PID) {
                        return getmypid();
                }
        }

}