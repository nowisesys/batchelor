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

namespace Batchelor\Logging;

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
         * The locale specific format (i.e. "Tue Feb 5 00:45:10 2009").
         */
        const DATETIME_HUMAN = "%c";
        /**
         * The locale specific format (i.e. "02/05/09 03:59:16").
         */
        const DATETIME_LOCALE = "%x %X";
        /**
         * The database ISO format (i.e. "2009-02-05 03:59:16").
         */
        const DATETIME_ISO_DATABASE = "%Y-%m-%d %H:%M:%S";
        /**
         * Format as UNIX epoch timestamp (i.e. "305815200").
         */
        const DATETIME_UNIX_EPOCH = "%s";
        /**
         * Log with seconds granularity.
         */
        const GRANULARITY_SECONDS = 1;
        /**
         * Log with microseconds granularity (int|int).
         */
        const GRANULARITY_MICROSEC = 2;
        /**
         * Log with microseconds granularity (float).
         */
        const GRANULARITY_FLOATSEC = 3;

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
         * The logging granularity.
         * @var int 
         */
        private $_granularity;
        /**
         * The datetime format string.
         * @var string 
         */
        private $_format = self::DATETIME_ISO_DATABASE;

        /**
         * Constructor.
         * 
         * @param string $filename The target filename.
         * @param string $ident The string ident is added to each message.
         * @param int $options The logging options (bitmask of zero or more LOG_XXX contants).
         * @param int $granularity The logging granularity (one of the GRANULARITY_XXX constats).
         */
        public function __construct(string $filename, string $ident = "", int $options = LOG_CONS | LOG_PID, int $granularity = self::GRANULARITY_SECONDS)
        {
                $this->_filename = $filename;
                $this->_ident = $ident;
                $this->_options = $options;
                $this->_granularity = $granularity;
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
         * Set logger granularity.
         * @param int $granularity The logging granularity (one of the GRANULARITY_XXX constats).
         */
        public function setGranularity(int $granularity)
        {
                $this->_granularity = $granularity;
        }

        /**
         * Set datetime format string.
         *  
         * @param string $format The format string.
         * @see strftime()
         */
        public function setFormat(string $format)
        {
                $this->_format = $format;
        }

        /**
         * {@inheritdoc}
         */
        public function message(int $priority, string $message, array $args = array()): bool
        {
                $format = vsprintf($message, $args);
                $result = $this->getFormatted($priority, $format);
                $this->append($result, $this->_filename);
                return true;
        }

        /**
         * Append message to logfile.
         * 
         * @param string $message The log message.
         * @param string $filename The target filename.
         * @throws RuntimeException
         */
        private function append(string $message, string $filename)
        {
                try {
                        if (!($handle = fopen($this->_filename, "a"))) {
                                throw new RuntimeException("Failed open $filename for write");
                        }
                        if (!flock($handle, LOCK_EX)) {
                                throw new RuntimeException("Failed lock $filename");
                        }
                        if (!fwrite($handle, $message) < 0) {
                                throw new RuntimeException("Failed write log message to $filename");
                        }
                } catch (RuntimeException $exception) {
                        if ($this->_options & LOG_CONS) {
                                fprintf(STDERR, $message);
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
         * Get formatted message.
         * 
         * @param int $priority One of the LOG_XXX constants.
         * @param string $message The message to log.
         */
        private function getFormatted(int $priority, string $message): string
        {
                return sprintf(
                    "%s [%s:%d] [%s] %s\n", $this->getTimestamp(), $this->_ident, $this->getProcess(), $this->getPriority($priority), $message
                );
        }

        /**
         * Get timestamp string.
         * @return string 
         */
        private function getTimestamp(): string
        {
                switch ($this->_granularity) {
                        case self::GRANULARITY_SECONDS:
                                return strftime($this->_format);
                        case self::GRANULARITY_MICROSEC:
                                return microtime();
                        case self::GRANULARITY_FLOATSEC:
                                return (string) microtime(true);
                }
        }

        /**
         * Get priority string.
         * @param int $priority The log priority.
         */
        private function getPriority(int $priority): string
        {
                static $priorities = [
                        LOG_EMERG   => "emergency",
                        LOG_ALERT   => "alert",
                        LOG_CRIT    => "critical",
                        LOG_ERR     => "error",
                        LOG_WARNING => "warning",
                        LOG_NOTICE  => "notice",
                        LOG_INFO    => "info",
                        LOG_DEBUG   => "debug"
                ];

                if (array_key_exists($priority, $priorities)) {
                        return $priorities[$priority];
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
