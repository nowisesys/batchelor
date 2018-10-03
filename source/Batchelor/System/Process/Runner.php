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

namespace Batchelor\System\Process;

use Batchelor\Logging\Logger;
use Batchelor\Logging\Target\Stream;
use RuntimeException;
use Throwable;

/**
 * The daemon runner.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Runner extends Daemon
{

        /**
         * The process object.
         * @var Daemonized 
         */
        private $_process;
        /**
         * The process logger.
         * @var Logger 
         */
        private $_logger;
        /**
         * Don't detach from controlling terminal.
         * @var bool 
         */
        private $_foreground = false;

        public function __construct(Daemonized $process)
        {
                parent::__construct();

                $this->_process = $process;
                $this->_logger = new Stream(STDERR);
        }

        /**
         * Set message logger.
         * @param Logger $logger The message logger,
         */
        public function setLogger(Logger $logger)
        {
                $this->_logger = $logger;
        }

        /**
         * Get message logger.
         * @return Logger
         */
        public function getLogger(): Logger
        {
                return $this->_logger;
        }

        /**
         * Run in foreground (for debug).
         */
        public function setForeground()
        {
                $this->_foreground = true;
        }

        /**
         * Execute process daemonized.
         */
        public function execute()
        {
                $this->prepare();

                $process = $this->_process;
                $process->prepare($this->_logger);

                if (!$this->_foreground) {
                        $this->setDetached();
                }

                do {
                        try {
                                $process->prepare($this->_logger);
                                $process->execute($this->_logger);
                        } catch (Throwable $exception) {
                                $this->onException($exception);
                        }
                } while (!$process->finished());
        }

        /**
         * Setup exception and signal handlers.
         * @throws RuntimeException
         */
        private function prepare()
        {
                if (!(set_exception_handler(function(Throwable $exception) {
                            $this->onException($exception);
                    }))) {
                        throw new RuntimeException("Failed setup exception handler");
                }
                if (!pcntl_signal(SIGTERM, function(int $signal) {
                            $this->onSignal($signal, "terminate");
                    })) {
                        throw new RuntimeException("Failed setup terminate signal handler");
                }
                if (!pcntl_signal(SIGINT, function(int $signal) {
                            $this->onSignal($signal, "interrupt");
                    })) {
                        throw new RuntimeException("Failed setup keyboard interrupt signal handler");
                }
        }

        /**
         * Called on exception.
         * @param Throwable $exception The trapped exception.
         */
        private function onException(Throwable $exception)
        {
                $this->_logger->error(sprintf(
                        "%s in %s line %s", $exception->getMessage(), basename($exception->getFile()), $exception->getLine()
                ));
        }

        /**
         * Called on signal.
         * 
         * @param int $signal The trapped signal.
         * @param string $name The signal name.
         */
        private function onSignal(int $signal, string $name)
        {
                $this->_logger->notice("Trapped signal $signal ($name)");

                switch ($signal) {
                        case SIGTERM:
                        case SIGINT:
                                $this->_logger->debug("Requesting processor to quit");
                                $this->_process->terminate($this->_logger);
                                break;
                }
        }

}
