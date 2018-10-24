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

use Batchelor\Logging\Logger;

/**
 * Capture output/error from command in logger.
 * 
 * <code>
 * // 
 * // Capture command output in memory logger:
 * // 
 * $logging = new MemoryLogger();
 * $capture = Capture::create($logging, "ls -l /tmp");
 * $capture->execute();
 * </code>
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Capture implements Selectable
{

        /**
         * The selectable command.
         * @var Selectable
         */
        private $_command;
        /**
         * The output logger.
         * @var Logger 
         */
        private $_logging;

        /**
         * Constructor.
         * 
         * @param Selectable $command The selectable command.
         * @param Logger $logging The output logger.
         */
        public function __construct(Selectable $command, Logger $logging)
        {
                $this->_command = $command;
                $this->_logging = $logging;
        }

        /**
         * Get command logger.
         * @return Logger
         */
        public function getLogger(): Logger
        {
                return $this->_logging;
        }

        /**
         * {@inheritdoc}
         */
        public function getCommand(): string
        {
                return $this->_command->getCommand();
        }

        /**
         * {@inheritdoc}
         */
        public function getDirectory()
        {
                return $this->_command->getDirectory();
        }

        /**
         * {@inheritdoc}
         */
        public function getEnvironment()
        {
                return $this->_command->getEnvironment();
        }

        /**
         * {@inheritdoc}
         */
        public function onError($stream)
        {
                while (($buff = trim(fgets($stream)))) {
                        $this->_logging->error($buff);
                }
        }

        /**
         * {@inheritdoc}
         */
        public function onOutput($stream)
        {
                while (($buff = trim(fgets($stream)))) {
                        $this->_logging->info($buff);
                }
        }

        /**
         * {@inheritdoc}
         */
        public function onInput($stream)
        {
                // Ignore
        }

        /**
         * Execute process.
         */
        public function execute()
        {
                (new Monitor($this))->execute();
        }

        /**
         * Create capture object.
         * 
         * @param Logger $logging The output logger.
         * @param string $command The command to execute.
         * @param array $env The environment variables.
         * @param string $cwd The working directory.
         * 
         * @return Capture
         */
        public static function create(Logger $logging, string $command, array $env = null, string $cwd = null): Capture
        {
                return new Capture(
                    new Runnable($command, $env, $cwd), $logging
                );
        }

}
