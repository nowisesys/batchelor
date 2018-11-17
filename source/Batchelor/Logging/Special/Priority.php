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

use Batchelor\Logging\Logger;
use Batchelor\Logging\Target\Adapter;
use Batchelor\Logging\Target\File;
use Batchelor\Logging\Writer;
use Batchelor\System\Service\Storage;
use Batchelor\System\Services;

/**
 * The message priority file logger.
 *
 * Log each message to different logs based on message priority. For example debug
 * messages goes to debug.log.
 * 
 * <code>
 * $logger = new Priority("/var/log/batchelor");
 * $logger->debug(...);         // Goes to debug.log
 * $logger->error(...);         // Goes to error.log
 * </code>
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Priority extends Adapter implements Logger
{

        /**
         * The target directory.
         * @var string 
         */
        private $_path;
        /**
         * The logger identity.
         * @var string 
         */
        private $_ident;
        /**
         * The file extension.
         * @var string 
         */
        private $_extension;
        /**
         * The logging options.
         * @var int
         */
        private $_options;
        /**
         * Special target files.
         * @var array 
         */
        private $_targets = [];

        /**
         * Constructor.
         * 
         * @param string $path The target directory (relative or absolute).
         * @param string $ident The string ident is added to each message.
         * @param string $extension The file extension.
         * @param int $options The logging options (bitmask of zero or more LOG_XXX contants).
         */
        public function __construct(string $path, string $ident = "", string $extension = "log", int $options = LOG_CONS | LOG_PID)
        {
                $this->_path = $this->getPathname($path);
                $this->_ident = $ident;
                $this->_extension = $extension;
                $this->_options = $options;
        }

        /**
         * Get target path.
         * 
         * Relative pathes will be created inside the data directory, otherwise
         * globally in the filesystem. The path is always created.
         * 
         * @param string $path The directory path.
         * @return string
         */
        private function getPathname($path): string
        {
                return $this->getDataStorage()
                        ->useDirectory($path)
                        ->getPathname();
        }

        /**
         * @return Storage
         */
        private function getDataStorage(): Storage
        {
                return Services::getInstance()->getService("data");
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
         * Set filename extension.
         * @param string $extension The filename extension (i.e. "log").
         */
        public function setExtension(string $extension)
        {
                $this->_extension = $extension;
        }

        /**
         * {@inheritdoc}
         */
        protected function doLogging(int $priority, string $message, array $args = array()): bool
        {
                return $this->getLogger($priority)
                        ->message($priority, $message, $args);
        }

        /**
         * Get file logger for priority.
         * 
         * @param int $priority The message priority.
         * @return File The file logger.
         */
        public function getLogger(int $priority): File
        {
                return new File($this->getFilename($priority), $this->_ident, $this->_options);
        }

        /**
         * Get filename for priority.
         * 
         * @param int $priority The message priority.
         * @return string The filename.
         */
        private function getFilename(int $priority): string
        {
                return sprintf(
                    "%s/%s.%s", $this->_path, $this->getTarget($priority), $this->_extension
                );
        }

        /**
         * Get priority to name mapping.
         * @return array
         */
        private function getPriorities(): array
        {
                return $priorities = [
                        LOG_EMERG   => "emergency",
                        LOG_ALERT   => "alert",
                        LOG_CRIT    => "critical",
                        LOG_ERR     => "error",
                        LOG_WARNING => "warning",
                        LOG_NOTICE  => "notice",
                        LOG_INFO    => "info",
                        LOG_DEBUG   => "debug"
                ];
        }

        /**
         * Set special target for this priority.
         * 
         * Supports override the default target for given message priority. Useful
         * if some priorities should be combined in a single file.
         * 
         * <code>
         * $logger = new Priority("/var/log/myapp");
         * $logger->setTarget(LOG_EMERG, "fatal");
         * $logger->setTarget(LOG_ALERT, "fatal");
         * $logger->setTarget(LOG_CRIT,  "fatal");
         * $logger->message(LOG_ALERT, "This message goes to the fatal log");
         * </code>
         * 
         * @param int $priority One of the LOG_XXX constants.
         * @param string $target The target name.
         */
        public function setTarget(int $priority, string $target)
        {
                $this->_targets[$priority] = $target;
        }

        /**
         * Get target name.
         * 
         * @param int $priority The message priority.
         * @return string The target name.
         */
        private function getTarget(int $priority): string
        {
                if (isset($this->_targets[$priority])) {
                        return $this->_targets[$priority];
                } else {
                        return $this->getPriorities()[$priority];
                }
        }

        /**
         * The file logger factory function.
         * 
         * @param array $options The logger options.
         * @return Writer
         */
        public static function create(array $options): Writer
        {
                if (!isset($options['ident'])) {
                        $options['ident'] = 'batchelor';
                }
                if (!isset($options['path'])) {
                        $options['path'] = sys_get_temp_dir() . "/" . $options['ident'];
                }
                if (!isset($options['extension'])) {
                        $options['extension'] = "log";
                }
                if (!isset($options['options'])) {
                        $options['options'] = LOG_CONS | LOG_PID;
                }

                return new Priority($options['path'], $options['ident'], $options['extension'], $options['options']);
        }

}
