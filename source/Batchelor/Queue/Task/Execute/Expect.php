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

use BadFunctionCallException;
use RuntimeException;

/**
 * The expect class.
 * 
 * Run interactive program using expect. Provides two methods: The process() is 
 * used to handle interaction with command passed to constructor and the execute() 
 * method is used to run command in a shell (for example if an SSH connection was
 * opened).
 * 
 * <code>
 * $expect = new Expect("/bin/bash");
 * $expect->execute("ls -l /tmp", static function($stream, $expect) { 
 *      while ($expect->isReadable()) { 
 *              print fgets($stream)
 *      }
 * });
 * </code>
 *
 * SSH-connection example. Pass an array of options for input processing. The 
 * third option is one of the EXP_XXX pattern matching constants. 
 * 
 * <code>
 * $expect = new Expect("ssh root@localhost");
 * // 
 * // The second option can also be a function. It's called with stream, the
 * // expect object and matches in first column pattern. Using a function might
 * // be a good option when the shell is detected. The function can return false
 * // to break the process loop.
 * // 
 * $expect->process([
 *      [ "password:", "secret" ],      // Send password.
 *      [ "(yes/no)",  "yes" ],         // Connection key.
 *      [ "~$ ", false ];               // Break process loop.
 * ]);
 * $expect->execute("ls -l /tmp", static function($stream, $expect) {  
 *      while ($expect->isReadable()) { 
 *              print fgets($stream)
 *      }
 * });
 * </code>
 * 
 * It's also possible to pass an callback function for processing command 
 * output:
 * 
 * <code>
 * $expect = new Expect("ssh root@localhost");
 * $expect->process(static function($stream, $expect) {
 *      while ($expect->isReadable()) { 
 *              $buff = trim(fgets($stream));
 *                      ...
 *              return false;   // Break process loop
 *      }
 * });
 * </code>
 * 
 * Stream returned by expect_popen() is blocking. Take care when reading response
 * from command.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Expect
{

        /**
         * The expect handle.
         * @var resource 
         */
        private $_handle;

        /**
         * Constructor.
         * @param string $command The command to execute.
         */
        public function __construct(string $command)
        {
                if (!extension_loaded("expect")) {
                        throw new BadFunctionCallException("The expect extension is not loaded");
                }
                if (!($this->_handle = expect_popen($command))) {
                        throw new RuntimeException("Failed open expect resource handle for $command");
                }
        }

        /**
         * Destructor.
         * @throws RuntimeException
         */
        public function __destruct()
        {
                if (!fclose($this->_handle)) {
                        throw new RuntimeException("Failed close expect handle");
                }
        }

        /**
         * Process output from command.
         * 
         * @param array|callback $options The processing options.
         * @return Expect 
         */
        public function process($options): Expect
        {
                if (is_callable($options)) {
                        $this->invoke($options);
                }
                if (is_array($options)) {
                        $this->runOptions($options);
                }

                return $this;
        }

        /**
         * Input command on stream.
         * 
         * The second option is the same as for process, with the addition of
         * accepting a bool for echo command output.
         * 
         * <code>
         * $expect->execute("ps xa");                   // Silent consume output
         * $expect->execute("ps xa", true);             // Output to stdout
         * $expect->execute("ps xa", function($stream, $expect) {
         *      while ($expect->isReadable()) {         // Process output
         *              $buff = fgets($stream);
         *      }
         * });
         * </code>
         * 
         * @param string $command The command to run.
         * @param array|callback|bool $options The processing options.
         * @return Expect 
         */
        public function execute(string $command, $options = null): Expect
        {
                if (!(fwrite($this->_handle, $command) == strlen($command))) {
                        throw new RuntimeException("Failed run command $command");
                }
                if (is_array($options) || is_callable($options)) {
                        $this->process($options);
                } elseif (is_bool($options) && $options) {
                        $this->invoke(function($stream, $expect) {
                                self::consume($stream, $expect, true);
                        });
                } else {
                        $this->invoke(function($stream, $expect) {
                                self::consume($stream, $expect);
                        });
                }

                return $this;
        }

        /**
         * Consume input buffer.
         * 
         * @param resource $stream The stream.
         * @param Expect $expect This object.
         * @param bool $echo Echo on stdout.
         */
        private static function consume($stream, Expect $expect, bool $echo = false)
        {
                while ($expect->isReadable()) {
                        if ($echo) {
                                printf("%s\n", trim(fgets($stream)));
                        } else {
                                fgets($stream);
                        }
                }
        }

        /**
         * Run input loop.
         * 
         * @param array $options The processing options.
         */
        private function runOptions(array $options)
        {
                $options = $this->getOptions($options);
                $matches = [];

                while ($this->control($options, $matches)) {
                        // Nothing to do here
                }

                return $this;
        }

        /**
         * Tranform options to expect control format.
         * 
         * @param array $options The processing options.
         * @return array
         */
        private function getOptions(array $options): array
        {
                $result = [];

                foreach ($options as $index => $data) {
                        if (!isset($data[0])) {
                                throw new RuntimeException("Missing match string in options");
                        }
                        if (!isset($data[1])) {
                                throw new RuntimeException("Missing match value in options");
                        }
                        if (!isset($data[2])) {
                                $data[2] = EXP_GLOB;
                        }

                        $result[$index] = [
                                $data[0],
                                $index,
                                $data[2],
                                $data[1]
                        ];
                }

                return $result;
        }

        /**
         * Process process output.
         * 
         * @param array $options
         * @param array $matches
         * @return boolean
         * @throws RuntimeException
         */
        private function control(array $options, array $matches): bool
        {
                switch (($index = expect_expectl($this->_handle, $options, $matches))) {
                        case EXP_EOF:
                                return false;
                        case EXP_TIMEOUT:
                                throw new RuntimeException("Timeout waiting for input");
                        case EXP_FULLBUFFER:
                                throw new RuntimeException("No pattern have been matched.");
                        default:
                                return $this->handle($options[$index][3], $matches);
                }
        }

        /**
         * Handle user option.
         * 
         * @param string|callback|boolean $option The user option value.
         * @param array $matches The matches from expect_expectl().
         * @return boolean
         */
        private function handle($option, array $matches): bool
        {
                if (is_bool($option)) {
                        return $option;
                }
                if (is_string($option)) {
                        $this->execute($option);
                        return true;
                }
                if (is_callable($option)) {
                        return $this->invoke($option, $matches);
                }
        }

        /**
         * Invoke user callback.
         * 
         * Set non-blocking mode on stream before invoking the user supplied
         * callback and restore stream blocking on return.
         * 
         * @param callable $option The callback function.
         * @param array $matches The matched pattern if any.
         * @return bool
         * @throws RuntimeException
         */
        private function invoke(callable $option, array $matches = null)
        {
                try {
                        if (!stream_set_blocking($this->_handle, false)) {
                                throw new RuntimeException("Failed set blocking mode on expect stream");
                        } else {
                                return $option($this->_handle, $this, $matches);
                        }
                } finally {
                        if (!stream_set_blocking($this->_handle, true)) {
                                throw new RuntimeException("Failed set blocking mode on expect stream");
                        }
                }
        }

        /**
         * Check if stream is ready for read.
         * 
         * Call this method in a loop from user supplied callback for checking
         * if stream is ready for read. This is needed because the expect stream 
         * is blocking by default.
         * 
         * Don't call this method when stream is blocking. The internally used 
         * stream select should only be used with non-blocking streams.
         * 
         * A short timeout is always applied, even when the parameter timeout has
         * been set to 0. 
         * 
         * @param int $timeout The timeout in seconds.
         * @return bool
         * @throws RuntimeException
         */
        public function isReadable(int $timeout = 0)
        {
                list($fdr, $fdw, $fde) = [[$this->_handle], null, null];

                if (($res = stream_select($fdr, $fdw, $fde, $timeout, 20000)) === false) {
                        throw new RuntimeException("Failed select");
                } elseif (feof($this->_handle)) {
                        return false;
                } else {
                        return $res > 0;
                }
        }

}
