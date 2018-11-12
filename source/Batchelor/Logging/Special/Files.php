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

/**
 * The multiple files logger.
 * 
 * This is basically a special case of the priority logger that combines high
 * priority messages into a common fatal log. The same is also done for notice 
 * and info priority messages. It also caches the file logger instance between 
 * each logging.
 * 
 * By default the following files gets incremental created on demand under the
 * log directory path:
 * 
 * <ul>
 * <li>fatal.log    (LOG_EMERG, LOG_ALERT, LOG_CRIT).</li>
 * <li>error.log    (LOG_ERR).</li>
 * <li>warning.log  (LOG_WARNING).</li>
 * <li>info.log     (LOG_NOTICE, LOG_INFO).</li>
 * <li>debug.log    (LOG_DEBUG).</li>
 * </ul>
 * 
 * Call setTarget() or setTargets() to override the default targets.
 * 
 * <code>
 * // 
 * // Separate log files for info and notice priority:
 * // 
 * $logger->setTarget(LOG_INFO, "info");
 * $logger->setTarget(LOG_NOTICE, "notice");
 * 
 * // 
 * // Same, but using an array:
 * // 
 * $logger->setTargets([
 *      LOG_INFO   => "info",
 *      LOG_NOTICE => "notice"
 * ]);
 * </code>
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Files extends Adapter implements Logger
{

        /**
         * The priority logger.
         * @var Priority 
         */
        private $_logger;
        /**
         * The file loggers.
         * @var array 
         */
        private $_loggers = [];

        /**
         * Constructor.
         * 
         * @param string $path The target directory.
         * @param string $ident The string ident is added to each message.
         * @param string $extension The file extension.
         * @param int $options The logging options (bitmask of zero or more LOG_XXX contants).
         */
        public function __construct(string $path, string $ident = "", string $extension = "log", int $options = LOG_CONS | LOG_PID)
        {
                $this->_logger = new Priority($path, $ident, $extension, $options);

                $this->_logger->setTarget(LOG_EMERG, "fatal");
                $this->_logger->setTarget(LOG_ALERT, "fatal");
                $this->_logger->setTarget(LOG_CRIT, "fatal");

                $this->_logger->setTarget(LOG_NOTICE, "info");
                $this->_logger->setTarget(LOG_INFO, "info");
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
         * Get file logger,
         * 
         * @param int $priority The message priority.
         * @return File
         */
        private function getLogger(int $priority)
        {
                if (!isset($this->_loggers[$priority])) {
                        return $this->_loggers[$priority] = $this->_logger->getLogger($priority);
                } else {
                        return $this->_loggers[$priority];
                }
        }

        /**
         * Set filename extension.
         * @param string $extension The filename extension (i.e. "log").
         */
        public function setExtension(string $extension)
        {
                $this->_loggers = [];
                $this->_logger->setExtension($extension);
        }

        /**
         * Set logger identity.
         * @param string $ident The string ident is added to each message.
         */
        public function setIdentity(string $ident)
        {
                $this->_loggers = [];
                $this->_logger->setIdentity($ident);
        }

        /**
         * Set logger options.
         * @param int $options The logging options (bitmask of zero or more LOG_XXX contants).
         */
        public function setOptions(int $options)
        {
                $this->_loggers = [];
                $this->_logger->setOptions($options);
        }

        /**
         * Set special target for this priority.
         * 
         * @param int $priority One of the LOG_XXX constants.
         * @param string $target The target name.
         * @see Priority::setTarget()
         */
        public function setTarget(int $priority, string $target)
        {
                $this->_loggers = [];
                $this->_logger->setTarget($priority, $target);
        }

        /**
         * Set special targets for given priorities.
         * 
         * <code>
         * $logger->setTargets([
         *      LOG_INFO   => "info",
         *      LOG_NOTICE => "notice"
         * ]);
         * </code>
         * 
         * @param array $priorities
         * @see Priority::setTarget()
         */
        public function setTargets(array $priorities)
        {
                $this->_loggers = [];
                foreach ($priorities as $priority => $target) {
                        $this->_logger->setTarget($priority, $target);
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

                return new Files($options['path'], $options['ident'], $options['extension'], $options['options']);
        }

}
