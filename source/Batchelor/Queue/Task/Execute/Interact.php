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
 * The interact class.
 * 
 * Support for running interactive programs. Those familiar with expect on UNIX 
 * should recognize the idea. The difference between expect and this class is
 * that methods are called in sequence.
 * 
 * <code>
 * // 
 * // Run some installer script:
 * // 
 * (new Interact(new Command(
 *      "./install.sh --destdir=/opt"
 * )))
 *      ->input("Do you accept the license (yes/no)? ", "yes")
 * </code>
 * 
 * Setting the slowness can be useful when executing commands inside an interactive
 * shell (i.e. bash or telnet):
 * 
 * <code>
 * (new Interact(new Command(
 *      "telnet localhost"
 * )))
 *      ->setSlowness(250000)
 *      ->match("/.*login:/", $user)
 *      ->match("/.*assword:/", $pass)
 *      ->run("ls -l /tmp --color=never\n", $output)
 *      ->run("exit\n");
 * </code>
 * 
 * This class could be used to run commands in a shell:
 * 
 * <code>
 * (new Interact(
 *  new Command("/bin/bash")
 * ))
 *      ->run("ls -l /tmp | grep sess_*\n", $output)
 *      ->run("exit\n");
 * </code>
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Interact
{

        /**
         * The command to run.
         * @var Command 
         */
        private $_command;
        /**
         * The debugger callback.
         * @var callback 
         */
        private $_debugger;
        /**
         * The slowness when running commands.
         * @var int 
         */
        private $_slowness = 150000;

        /**
         * Constructor.
         * @param Command $command The command to run.
         */
        public function __construct($command)
        {
                $this->_command = $command;
        }

        /**
         * Set debug message callback.
         * 
         * <code>
         * $interact->setDebug(static function($message) {
         *      printf("[DEBUG]: %s\n", $message);
         * })
         * </code>
         * 
         * @param callable $callback The callback function.
         * @return Interact
         */
        public function setDebug(callable $callback): Interact
        {
                $this->_debugger = $callback;

                return $this;
        }

        /**
         * Set slowness waitning for response.
         * 
         * The number of microsec to pause while waitning for response from an
         * executed command. Only used by run() when running a command in an
         * interactive shell.
         * 
         * @param int $microsec The number of micro seconds.
         */
        public function setSlowness(int $microsec)
        {
                $this->_slowness = $microsec;
        }

        /**
         * Scan output for prompt.
         * 
         * @param string $prompt The text to search for (regexp or just text).
         * @param callable $compare Called to compare.
         * @param callable $action Called if matched.
         * @return Interact
         */
        private function scan(string $prompt, callable $compare, callable $action)
        {
                if (($line = $this->next($prompt, $compare))) {
                        $action($line);
                }

                $this->read(10);
                return $this;
        }

        /**
         * Match string prompt.
         * 
         * If the prompt match, send answer to running process. Matching is done
         * exact using regex or string compare.
         * 
         * @param string $prompt The prompt text.
         * @param string $answer The answer to send.
         * @param bool $regex Use regex compare for comparing prompt.
         * 
         * @return Interact
         */
        public function input(string $prompt, string $answer, bool $regex = true): Interact
        {
                if ($regex) {
                        return $this->scan($prompt, function($prompt, $line) {
                                    return preg_match($prompt, $line);
                            }, function() use($answer) {
                                    $this->_command->setInput($answer);
                            });
                } else {
                        return $this->scan($prompt, function($prompt, $line) {
                                    return strcmp($prompt, $line) == 0;
                            }, function() use($answer) {
                                    $this->_command->setInput($answer);
                            });
                }
        }

        /**
         * Match string prompt.
         * 
         * Calls the answer callback if prompt match. The executed command object 
         * and matching line will be passed as arguments.
         * 
         * <code>
         * // 
         * // Send username when prompted:
         * // 
         * $interact->match("/.*login:/", function($command, $line) use($username) {
         *      $command->setInput($username);
         * });
         * </code>
         * 
         * @param string $prompt The prompt text.
         * @param callable $answer The supplied callback.
         * @param bool $regex Use regex compare for comparing prompt.
         * 
         * @return Interact
         */
        public function match(string $prompt, callable $answer, bool $regex = true): Interact
        {
                if ($regex) {
                        return $this->scan($prompt, function($prompt, $line) {
                                    return preg_match($prompt, $line);
                            }, function($line) use($answer) {
                                    $answer($this->_command, $line);
                            });
                } else {
                        return $this->scan($prompt, function($prompt, $line) {
                                    return strcmp($prompt, $line) == 0;
                            }, function($line) use($answer) {
                                    $answer($this->_command, $line);
                            });
                }
        }

        /**
         * Set output buffer.
         * @param string $buff The message for output.
         * @param string $output The output buffer.
         */
        private function output(string $buff, string &$output = null)
        {
                if (isset($this->_debugger)) {
                        // $this->debug($buff);
                }
                if (isset($output)) {
                        $output .= $buff;
                }
        }

        /**
         * Read command output.
         * 
         * @param int $timeout The timeout waiting for data (max).
         * @param string $output The output buffer.
         */
        private function read(int $timeout = 0, string &$output = null)
        {
                $command = $this->_command;

                if ($command->hasOutput($timeout)) {
                        while (($buff = $command->getOutput())) {
                                $this->output($buff, $output);
                        }
                }
        }

        /**
         * Send command input.
         * 
         * The use case for this method is when this class is executing an 
         * interactive shell (i.e. telnet or bash). This method inputs the
         * command string on shells stdin and reads response.
         * 
         * @param string $command The input command.
         * @param string $output The output string.
         */
        public function run(string $command, string &$output = null): Interact
        {
                $this->debug("RUN: $command");
                $this->_command->setInput($command);

                // 
                // Heres the deal: 
                // 
                // After issuing the command for the shell, we need to wait for 
                // response to build up. Starting to read to soon will make the
                // stream_select return > 0, but reading response will not give
                // the complete answer.
                // 
                usleep($this->_slowness);
                $this->read(10, $output);

                return $this;
        }

        /**
         * Match prompt.
         * 
         * @param string $prompt The prompt to match.
         * @param callable $compare The compare callback.
         * @return string|boolean
         */
        private function next(string $prompt, callable $compare)
        {
                while ($this->_command->hasOutput(5)) {
                        $input = trim($this->_command->getOutput(false));
                        $lines = explode("\n", $input);

                        if (count($lines) == 0) {
                                return false;
                        }

                        foreach ($lines as $line) {
                                $line = trim($line);
                                $this->debug($line);

                                if (empty($line)) {
                                        continue;
                                } elseif ($compare($prompt, $line)) {
                                        return $line;
                                }
                        }
                }

                return false;
        }

        /**
         * Write message to debugger.
         * @param string $message The message string.
         */
        private function debug(string $message)
        {
                if (($debugger = $this->_debugger)) {
                        $debugger($message);
                }
        }

}
