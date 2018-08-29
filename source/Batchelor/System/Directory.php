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

namespace Batchelor\System;

use Batchelor\System\Directory\Cleanup;
use Batchelor\System\Directory\Scanner;
use RuntimeException;

/**
 * The directory class.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Directory
{

        /**
         * The directory path.
         * @var string 
         */
        private $_path;
        /**
         * The directory name,
         * @var string 
         */
        private $_name;

        /**
         * Constructor.
         * @param string $path The directory path.
         */
        public function __construct(string $path = null)
        {
                $this->setPath($path);
        }

        /**
         * Set directory path.
         * @param string $path The directory path.
         */
        public function setPath(string $path)
        {
                $this->_path = $path;
                $this->_name = basename($path);
        }

        /**
         * Create directory.
         * 
         * @param int $mode The directory permission.
         * @throws RuntimeException
         */
        public function create(int $mode = 0755)
        {
                if (!mkdir($this->_path, $mode, true)) {
                        throw new RuntimeException(sprintf("Failed create directory %s", $this->_name));
                }
        }

        /**
         * Delete directory.
         * 
         * @param bool $recursive Delete non-empty directory.
         * @throws RuntimeException
         */
        public function delete(bool $recursive = true)
        {
                if ($recursive) {
                        $this->cleanup();
                }
                if (!rmdir($this->_path)) {
                        throw new RuntimeException(sprintf("Failed delete directory %s", $this->_name));
                }
        }

        /**
         * Cleanup inside directory.
         * @throws RuntimeException
         */
        public function cleanup()
        {
                $this->remove($this->_path);
        }

        /**
         * Cleanup helper function.
         * @param string $root The root path.
         * @throws RuntimeException
         */
        private function remove(string $root)
        {
                $cleanup = new Cleanup($root);
                $cleanup->startProcess();
        }

        /**
         * Read file in directory.
         * 
         * The filename is relative to directory path.
         * 
         * @param string $file The file to read.
         * @param bool $return Return file content.
         * @return string The file content.
         */
        public function read($file, $return = true)
        {
                $path = sprintf("%s/%s", $this->_path, $file);

                if (!file_exists($path)) {
                        throw new RuntimeException("The file $file is missing");
                }
                if (!is_readable($file)) {
                        throw new RuntimeException("The file $file is not readable");
                }

                if ($return) {
                        return file_get_contents($path);
                } else {
                        readfile($path);
                }
        }

        /**
         * Read files in directory.
         * 
         * Return a list of all files in current directory.
         * 
         * @param int $options Skip files options (i.e. no dots).
         * @param int $format The filename format (default to anchored at directory root).
         * @return string[] The list of files.
         */
        public function scan($options = Scanner::SKIP_DOTS, $format = Scanner::FILENAME_ANCHORED)
        {
                $scanner = new Scanner($this->_path);
                $scanner->setRecursive();
                $scanner->setOptions($options);
                $scanner->setFormat($format);

                return $scanner->getFiles();
        }

}
