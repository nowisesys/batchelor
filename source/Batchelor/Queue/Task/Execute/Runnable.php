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
 * The runnable command.
 * 
 * <code>
 * // 
 * // Example usage as an anonymous class:
 * // 
 * (new Process(
 *  new class("ls -l /tmp") extends Runnable {
 *      public function onOutput($stream)
 *      {
 *              while(($buff = fgets($stream))) {
 *                      printf("%s\n", rtrim($buff));
 *              }
 *      }
 *  }))->execute();
 * </code>
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Runnable implements Selectable
{

        /**
         * The command string.
         * @var string 
         */
        private $_cmd;
        /**
         * The environment variables.
         * @var array 
         */
        private $_env;
        /**
         * The working directory.
         * @var string 
         */
        private $_cwd;

        /**
         * Constructor.
         * @param string $cmd The command string.
         * @param array $env The environment variables.
         * @param string $cwd The working directory.
         */
        public function __construct(string $cmd = null, array $env = null, string $cwd = null)
        {
                $this->_cmd = $cmd;
                $this->_env = $env;
                $this->_cwd = $cwd;
        }

        /**
         * {@inheritdoc}
         */
        public function getCommand(): string
        {
                return $this->_cmd;
        }

        /**
         * {@inheritdoc}
         */
        public function getDirectory()
        {
                return $this->_cwd;
        }

        /**
         * {@inheritdoc}
         */
        public function getEnvironment()
        {
                return $this->_env;
        }

        /**
         * {@inheritdoc}
         */
        public function onError($stream)
        {
                // Ignore
        }

        /**
         * {@inheritdoc}
         */
        public function onOutput($stream)
        {
                // Ignore
        }

        /**
         * {@inheritdoc}
         */
        public function onInput($stream)
        {
                // Ignore
        }

}
