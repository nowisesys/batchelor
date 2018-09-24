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
 * The running process.
 * 
 * Core class not intended to be used direct. Instead, instances of this class 
 * will be created to handle interaction with the running process.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Process implements ProcessControl
{

        /**
         * The process handle.
         * @var resource 
         */
        private $_handle;
        /**
         * The pipe streams.
         * @var array 
         */
        private $_stream;
        /**
         * The exit status.
         * @var int 
         */
        private $_status = -1;

        /**
         * Constructor.
         * 
         * @param resource $handle The process handle.
         * @param array $streams The process streams.
         */
        public function __construct($handle, array $streams)
        {
                $this->_handle = $handle;
                $this->_stream = $streams;
        }

        /**
         * Destructor.
         */
        public function __destruct()
        {
                if (is_resource($this->_handle)) {
                        $this->close();
                }
        }

        /**
         * {@inheritdoc}
         */
        public function signal(int $signal): bool
        {
                return proc_terminate($this->_handle, $signal);
        }

        /**
         * {@inheritdoc}
         */
        public function suspend(): bool
        {
                return proc_terminate($this->_handle, SIGSTOP);
        }

        /**
         * {@inheritdoc}
         */
        public function resume(): bool
        {
                return proc_terminate($this->_handle, SIGCONT);
        }

        /**
         * {@inheritdoc}
         */
        public function terminate(): bool
        {
                return proc_terminate($this->_handle);
        }

        /**
         * Get command streams.
         * @return array
         */
        public function getStreams(): array
        {
                return $this->_stream;
        }

        /**
         * Get I/O stream.
         * 
         * @param int $fd The file descriptor.
         * @return resource
         */
        public function getStream(int $fd)
        {
                return $this->_stream[$fd];
        }

        /**
         * Get exit status.
         * @return int
         */
        public function getExitCode(): int
        {
                return $this->_status;
        }

        /**
         * Get process status.
         * 
         * @return Status
         */
        public function getStatus(): Status
        {
                $status = new Status($this->_handle);

                // 
                // See http://php.net/manual/en/function.proc-get-status.php
                // 
                if ($this->_status == -1) {
                        if ($status->running == false && $status->exitcode != -1) {
                                $this->_status = $status->exitcode;
                        }
                }

                return $status;
        }

        /**
         * Check bytes available.
         * 
         * Uses select to poll if stream is ready for read. Notice that if timeout 
         * is 0, then the call will return immediate. Pass null as timeout to block
         * caller infinite waiting for stream to become readable.
         * 
         * @param int $fd The file descriptor.
         * @param int $timeout The number of seconds to wait.
         * @return bool
         */
        public function isReadable(int $fd, int $timeout = 0): bool
        {
                $stream = $this->_stream[$fd];

                if (!is_resource($stream) || feof($stream)) {
                        return false;
                }

                list($r, $w, $e) = [[$stream], null, null];

                if ((stream_select($r, $w, $e, $timeout, 10000))) {
                        return true;
                } else {
                        return false;   // When error or none ready
                }
        }

        /**
         * Close process.
         */
        public function close()
        {
                foreach ($this->_stream as $index => $stream) {
                        if ($index != 0) {
                                while (!feof($stream)) {
                                        fread($stream, 1024);
                                }
                        }
                        if (fclose($stream) == false) {
                                throw new RuntimeException("Failed close stream");
                        }
                }
                if (is_resource($this->_handle)) {
                        $this->setExitCode(proc_close($this->_handle));
                }
        }

        /**
         * Set process exit code.
         * 
         * @param int $status The status code.
         * @throws RuntimeException
         */
        private function setExitCode(int $status)
        {
                if ($this->_status == -1) {
                        if ($status == -1 && $this->_status == -1) {
                                throw new RuntimeException("Failed close process");
                        } else {
                                $this->_status = $status;
                        }
                }
        }

}
