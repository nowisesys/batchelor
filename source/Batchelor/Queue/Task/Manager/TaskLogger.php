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

namespace Batchelor\Queue\Task\Manager;

use Batchelor\Logging\Target\Memory;
use Batchelor\Storage\File;
use RuntimeException;
use Throwable;

/**
 * Capture output from task.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class TaskLogger
{

        /**
         * The logger object.
         * @var Memory 
         */
        private $_logger;
        /**
         * The logfile.
         * @var File 
         */
        private $_file;

        /**
         * Constructor.
         * 
         * @param Memory $logger The logger object.
         */
        public function __construct(Memory $logger = null)
        {
                $this->_logger = $logger;
        }

        /**
         * Set logfile.
         * @param File $file The filename.
         */
        public function setLogfile(File $file)
        {
                $this->_file = $file;
        }

        /**
         * Set memory logger.
         * @param Memory $logger The logger object.
         */
        public function setLogger(Memory $logger)
        {
                $this->_logger = $logger;
        }

        /**
         * Log exception.
         * @param Throwable $exception The exception object.
         */
        public function logException(Throwable $exception)
        {
                $this->_logger->critical("%s: %s", [get_class($exception), $exception->getMessage()]);
        }

        /**
         * Start capture output.
         * @throws RuntimeException
         */
        public function start()
        {
                $logger = $this->_logger;

                if (!ob_start(function($buffer) use($logger) {

                            if (strlen($buffer) == 0) {
                                    return $buffer;
                            } elseif (!($lines = explode("\n", $buffer))) {
                                    return $buffer;
                            }

                            if (count($lines) == 0) {
                                    return $buffer;
                            } else {
                                    $logger->info("** Captured output: **");
                            }

                            foreach ($lines as $line) {
                                    if (($line = trim($line))) {
                                            $logger->info($line);
                                    }
                            }
                    })) {
                        throw new RuntimeException("Failed start output buffering");
                }
        }

        /**
         * Stop capture output.
         * @throws RuntimeException
         */
        public function stop()
        {
                if (!ob_end_flush()) {
                        throw new RuntimeException("Failed stop output buffering");
                }
        }

        /**
         * Flush memory logger. 
         */
        public function flush()
        {
                $this->write($this->_logger, $this->_file);
                $this->clean();
        }

        /**
         * Clean output buffer.
         */
        public function clean()
        {
                if (ob_get_level() != 0) {
                        ob_clean();
                }
                if ($this->_logger instanceof Memory) {
                        $this->_logger->clear();
                }
        }

        /**
         * Write content in memory logger to current selected logfile.
         * 
         * @param Memory $logger The memory logger.
         * @param File $file The target logfile.
         */
        private function write(Memory $logger, File $file)
        {
                if ($this->_logger instanceof Memory &&
                    $this->_logger->hasMesssages()) {
                        $file->putContent(
                            implode("\n", $logger->getMessages()) . "\n", FILE_APPEND
                        );
                }
        }

}
