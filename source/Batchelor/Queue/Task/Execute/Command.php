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

namespace Batchelor\Queue\Task\Execute;

use RuntimeException;

/**
 * The standalone command.
 * 
 * <code>
 * $command = new Command("ls -l /tmp");
 * $listing = $command->getOutput();
 * </code>
 * 
 * Streams are opened in non-blocking mode. Set blocking mode if you want to
 * wait for data to become readable:
 *
 * <code>
 * $command->setBlocking(1, true);
 * $listing = $command->getOutput();
 * </code>
 * 
 * @author Anders Lövgren (Nowise Systems)
 * @see Worker
 */
class Command
{

        /**
         * The running process.
         * @var Process 
         */
        protected $_process;
        /**
         * The console stream (/dev/tty).
         * @var resource 
         */
        protected $_console;

        /**
         * Constructor.
         * @param string $cmd The command string.
         * @param array $env The environment variables.
         * @param string $cwd The working directory.
         */
        public function __construct(string $cmd, array $env = null, string $cwd = null)
        {
                $this->_process = (new Spawner(
                    new Runnable($cmd, $env, $cwd)
                    ))->open();
        }

        /**
         * Write input message.
         * 
         * The message is written to stream resource connected with stdin of
         * running process.
         * 
         * @param string $message The message string.
         * @return int The number of bytes written.
         */
        public function setInput(string $message): int
        {
                if (($stream = $this->_process->getStream(0))) {
                        return $this->write($stream, $message);
                }
        }

        /**
         * Set console stream.
         * 
         * @param resource $stream The console stream.
         */
        public function setConsole($stream)
        {
                $this->_console = $stream;
        }

        /**
         * Read console message.
         * 
         * The console stream has to be set before calling this method.
         * 
         * @param bool $readline Get single line of input in text mode.
         * @param int $length The maximum number of bytes to read.
         * @return string
         * @see setConsole()
         */
        public function getConsole(bool $readline = true, int $length = 1024): string
        {
                if (($stream = $this->_console)) {
                        return $this->read($stream, $readline, $length);
                }
        }

        /**
         * Read output message.
         * 
         * The message is read from stream resource connected with stdout of
         * running process. In general, timeout should only be used if stream was
         * opened non-blocking.
         * 
         * @param bool $readline Get single line of input in text mode.
         * @param int $length The maximum number of bytes to read.
         * @param int $timeout The timeout waiting for data.
         * @return string
         */
        public function getOutput(bool $readline = true, int $length = 1024, int $timeout = 0): string
        {
                if ($timeout != 0 && !$this->_process->isReadable(1, $timeout)) {
                        return "";
                }
                if (($stream = $this->_process->getStream(1))) {
                        return $this->read($stream, $readline, $length, 1);
                }
        }

        /**
         * Read error message.
         * 
         * The message is read from stream resource connected with stderr of
         * running process.
         * 
         * @param bool $readline Get single line of input in text mode.
         * @param int $length The maximum number of bytes to read.
         * @param int $timeout The timeout waiting for data.
         * @return string
         */
        public function getError(bool $readline = true, int $length = 1024, int $timeout = 0): string
        {
                if ($timeout != 0 && !$this->_process->isReadable(2, $timeout)) {
                        return "";
                }
                if (($stream = $this->_process->getStream(2))) {
                        return $this->read($stream, $readline, $length);
                }
        }

        /**
         * Check if output exists.
         * 
         * Return true if output stream contains data that can be read immediate
         * without blocking. If timeout != 0 and input buffer is empty, then the 
         * calling process will wait maximum this number of seconds for data to 
         * become available.
         * 
         * @param int $timeout The number of seconds to wait.
         * @return bool
         */
        public function hasOutput(int $timeout = 0): bool
        {
                if (($stream = $this->_process->getStream(1))) {
                        if (feof($stream)) {
                                return false;
                        } else {
                                return $this->_process->isReadable(1, $timeout);
                        }
                }
        }

        /**
         * Return true if all output has been read.
         * 
         * @return bool
         */
        public function isFinished(): bool
        {
                if (($stream = $this->_process->getStream(1))) {
                        return feof($stream) == true;
                }
        }

        /**
         * Set blocking mode on stream.
         * 
         * @param int $fd The file descriptor.
         * @param bool $enable The enable/disable mode.
         * @throws RuntimeException
         */
        public function setBlocking(int $fd, bool $enable)
        {
                if (($stream = $this->_process->getStream($fd))) {
                        if (!stream_set_blocking($stream, $enable)) {
                                throw new RuntimeException("Failed set blocking mode on stream $fd");
                        }
                }
        }

        /**
         * Write output to stream.
         * 
         * @param resource $stream The process or console stream.
         * @param string $message The message string.
         * @return int The number of bytes written.
         */
        private function write($stream, string $message): int
        {
                try {
                        return fwrite($stream, $message);
                } finally {
                        fflush($stream);
                }
        }

        /**
         * Read input from stream.
         * 
         * @param resource $stream The process or console stream.
         * @param bool $readline Get single line of input in text mode.
         * @param int $length The wanted number of bytes.
         * @return string
         */
        private function read($stream, bool $readline, int $length): string
        {
                if ($readline) {
                        return fgets($stream, $length);
                } else {
                        return fread($stream, $length);
                }
        }

}
