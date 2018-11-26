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

use RuntimeException;

/**
 * Daemonize user process.
 *
 * This class should only be used in CLI mode. Initialize this class when PHP_SAPI 
 * is not CLI will throw an runtime exception.
 * 
 * <code>
 * $daemonizer = new Daemonize();
 * 
 * $daemonizer->setOption(Daemonize::NO_CHANGE_DIR, false);
 * $daemonizer->setOption(Daemonize::NO_CLOSE_FILE, false);
 * 
 * $daemonizer->perform();
 * </code>
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Daemonizer
{

        /**
         * Don't change directory to '/'.
         */
        const NO_CHANGE_DIR = 1;
        /**
         * Don't close standard streams.
         */
        const NO_CLOSE_FILE = 2;

        /**
         * The daemon options.
         * @var array 
         */
        private $_options = [];
        /**
         * The callback function.
         * @var callback 
         */
        private $_callback;

        /**
         * Constructor.
         * @throws RuntimeException
         */
        public function __construct()
        {
                if (PHP_SAPI != "cli") {
                        throw new RuntimeException("The daemonizer should only be used in CLI mode.");
                }

                if (!extension_loaded("pcntl")) {
                        throw new RuntimeException("The pcntl extension is not loaded");
                }
                if (!extension_loaded("posix")) {
                        throw new RuntimeException("The posix extension is not loaded");
                }

                $this->setOptions([
                        self::NO_CHANGE_DIR => false,
                        self::NO_CLOSE_FILE => false
                ]);
        }

        /**
         * Set option value.
         * 
         * @param int $option The option (one of NO_XXX constants).
         * @param bool $enable The option value.
         */
        public function setOption(int $option, bool $enable)
        {
                $this->_options[$option] = $enable;
        }

        /**
         * Set all options.
         * @param array $options Array of NO_XXX options.
         */
        public function setOptions(array $options)
        {
                $this->_options = $options;
        }

        /**
         * On daemonize complete.
         * 
         * @param callable $callback The callback.
         */
        public function onCompleted(callable $callback)
        {
                $this->_callback = $callback;
        }

        /**
         * Daemonize the calling process.
         * 
         * @throws RuntimeException
         */
        public function perform()
        {
                switch (pcntl_fork()) {
                        case -1:
                                throw new RuntimeException("Failed fork process");
                        case 0:
                                break;
                        default :
                                exit(0);
                }

                if (posix_setsid() == -1) {
                        throw new RuntimeException("Failed become session leader (setsid)");
                }

                switch (pcntl_fork()) {
                        case -1:
                                throw new RuntimeException("Failed fork process");
                        case 0:
                                umask(0);
                                break;
                        default :
                                exit(0);
                }

                if ($this->_options[self::NO_CHANGE_DIR] == false) {
                        if (!chdir('/')) {
                                throw new RuntimeException("Failed change working directory to '/'");
                        }
                }

                if ($this->_options[self::NO_CLOSE_FILE] == false) {
                        if (is_resource(STDIN) && !fclose(STDIN)) {
                                throw new RuntimeException("Failed close stdin stream");
                        }
                        if (is_resource(STDOUT) && !fclose(STDOUT)) {
                                throw new RuntimeException("Failed close stdout stream");
                        }
                        if (is_resource(STDERR) && !fclose(STDERR)) {
                                throw new RuntimeException("Failed close stderr stream");
                        }
                }

                // 
                // The calling process has now been deamonized and disconnected from 
                // controlling terminal (no pseudo tty). Calling posix_getppid() will not 
                // return 1, but 'ps jax' shows that the forked child has been adopted by
                // the init process (pid 1).
                // 
                
                if (($callback = $this->_callback)) {
                        $callback(getmypid());
                }
        }

}
