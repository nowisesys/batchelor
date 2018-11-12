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

/**
 * The process worker.
 * 
 * Call open() to actually execute the command. To get exit status from command, 
 * the worker has to be closed first by calling close(). The close() method is 
 * implicit called by destructor.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Worker
{

        /**
         * The command to run.
         * @var Selectable 
         */
        private $_command;
        /**
         * The running process.
         * @var Process 
         */
        private $_process;

        /**
         * Constructor.
         * @param Selectable $command
         */
        public function __construct(Selectable $command)
        {
                $this->_command = $command;
        }

        /**
         * Get selectable command.
         * 
         * @return Selectable
         */
        public function getSelectable(): Selectable
        {
                return $this->_command;
        }

        /**
         * Get running process.
         * @return Process 
         */
        public function getProcess(): Process
        {
                return $this->_process;
        }

        /**
         * Get command streams.
         * @return array
         */
        public function getStreams(): array
        {
                return $this->_process->getStreams();
        }

        /**
         * Get I/O stream.
         * 
         * @param int $fd The file descriptor.
         * @return resource
         */
        public function getStream(int $fd)
        {
                return $this->_process->getStream($fd);
        }

        /**
         * Create new worker object.
         * 
         * The command associated with the worker has not yet been executed. Call
         * open() on returned object to actually execute the command passed as the
         * cmd argument.
         * 
         * @param string $cmd The command string.
         * @param array $env The environment variables.
         * @param string $cwd The current working directory.
         * 
         * @return Worker
         */
        public static function create(string $cmd, array $env = null, string $cwd = null): Worker
        {
                return new self(
                    new Runnable($cmd, $env, $cwd)
                );
        }

        /**
         * Opens new worker object.
         * 
         * Creates an worker object and call open() internal. If successful the 
         * streams connected with the worker is ready for read/write.
         * 
         * @param string $cmd The command string.
         * @param array $env The environment variables.
         * @param string $cwd The current working directory.
         * 
         * @return Worker
         */
        public static function execute(string $cmd, array $env = null, string $cwd = null): Worker
        {
                return (new self(
                    new Runnable($cmd, $env, $cwd)
                    ))->open();
        }

        /**
         * Open process.
         */
        public function open()
        {
                $this->_process = (new Spawner($this->_command))->open();
        }

        /**
         * Close process.
         */
        public function close()
        {
                $this->_process->close();
                $this->_process = null;
        }

        /**
         * Check if process has been opened.
         * 
         * Returns true if the process handle has been opened, but not yet 
         * been closed. This does not mean that any data will be available
         * for read from either standard output or error stream.
         * 
         * @return bool 
         */
        public function isOpened(): bool
        {
                return isset($this->_process);
        }

}
