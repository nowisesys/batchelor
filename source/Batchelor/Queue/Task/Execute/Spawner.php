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
 * The process spawner.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Spawner
{

        /**
         * The command to spawn.
         * @var Selectable 
         */
        private $_command;

        /**
         * Constructor.
         * @param Selectable $command The command to spawn.
         */
        public function __construct(Selectable $command)
        {
                $this->_command = $command;
        }

        /**
         * Set command to spawn.
         * @param Selectable $command
         */
        public function setCommand(Selectable $command)
        {
                $this->_command = $command;
        }

        /**
         * Get command to spawn.
         * @return Selectable
         */
        public function getCommand(): Selectable
        {
                return $this->_command;
        }

        /**
         * Open process.
         * @throws RuntimeException
         */
        public function open(): Process
        {
                $options = array_values(
                    $this->getOptions($this->_command)
                );

                if (($handle = proc_open(...$options)) === false) {
                        throw new RuntimeException("Failed execute command");
                }

                if (!stream_set_blocking($options[2][0], false)) {
                        throw new RuntimeException("Failed set non-blocking mode on stream");
                }
                if (!stream_set_blocking($options[2][1], false)) {
                        throw new RuntimeException("Failed set non-blocking mode on stream");
                }
                if (!stream_set_blocking($options[2][2], false)) {
                        throw new RuntimeException("Failed set non-blocking mode on stream");
                }

                return new Process($handle, $options[2]);
        }

        /**
         * Get file descriptor specification.
         * @return array
         */
        private function getDescriptors(): array
        {
                return [
                        0 => array("pipe", "r"),
                        1 => array("pipe", "w"),
                        2 => array("pipe", "w"),
                ];
        }

        /**
         * Get command options.
         * @return array
         */
        private function getOptions(Selectable $command): array
        {
                return [
                        'cmd'   => $command->getCommand(),
                        'descr' => $this->getDescriptors(),
                        'pipes' => [],
                        'cwd'   => $command->getDirectory(),
                        'env'   => $command->getEnvironment()
                ];
        }

}
