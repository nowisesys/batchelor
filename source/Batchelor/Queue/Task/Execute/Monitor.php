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
 * The process monitor.
 * 
 * Executes the command and monitor it's process streams for activity using stream 
 * select. Calls on input, output or error methods on the selectable command when 
 * activity is detected on respective I/O stream.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Monitor
{

        /**
         * The worker command.
         * @var Worker 
         */
        private $_command;

        /**
         * Constructor.
         * @param Selectable $command The command to run.
         */
        public function __construct(Selectable $command)
        {
                $this->_command = new Worker($command);
        }

        /**
         * Execute command.
         * @param Selectable $cmd The command to run.
         */
        public function execute()
        {
                $command = $this->_command;
                $command->open();
                $streams = $command->getStreams();

                $this->loop($streams);

                $command->close();
        }

        /**
         * Process command I/O.
         * @param array $streams The command streams.
         */
        private function loop(array $streams)
        {
                while ($this->select($streams)) {
                        
                }
        }

        /**
         * Select on streams.
         * 
         * @param array $streams The command streams.
         * @throws RuntimeException
         */
        private function select(array $streams)
        {
                if (feof($streams[1])) {
                        return false;
                }

                $fdr = [$streams[1], $streams[2]];
                $fdw = [$streams[0]];
                $fde = null;

                if (($num = stream_select($fdr, $fdw, $fde, null)) === false) {
                        throw new RuntimeException("Failed select on streams");
                }

                $fds = array_merge($fdw, $fdr);
                return $this->process($streams, $fds);
        }

        /**
         * Process detected activity.
         * 
         * @param array $streams The command streams.
         * @param array $fds The selected streams.
         */
        private function process(array $streams, array $fds)
        {
                $command = $this->_command->getSelectable();

                foreach ($fds as $fd) {
                        if (feof($fd) && $fd == $streams[1]) {
                                return false;
                        } elseif ($fd == $streams[0]) {
                                $command->onInput($fd);
                        } elseif ($fd == $streams[1]) {
                                $command->onOutput($fd);
                        } elseif ($fd == $streams[2]) {
                                $command->onError($fd);
                        }
                }

                return true;
        }

}
