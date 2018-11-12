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

namespace Batchelor\Storage\Directory;

use DirectoryIterator;
use RuntimeException;
use SplFileInfo;

/**
 * Directory cleanup class.
 * 
 * This is an helper class providing cleanup functionality for file
 * system directories.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Cleanup
{

        /**
         * The root directory.
         * @var string 
         */
        private $_root;
        /**
         * The directory name.
         * @var string 
         */
        private $_name;

        /**
         * Constructor.
         * @param string $root The root directory.
         */
        public function __construct(string $root = null)
        {
                $this->setDirectory($root);
        }

        /**
         * Set directory path.
         * 
         * @param string $root The root directory.
         */
        public function setDirectory($root)
        {
                $this->_root = realpath($root);
                $this->_name = basename($this->_root);
        }

        /**
         * Start cleanup.
         * 
         * @param string $root The root directory.
         * @throws RuntimeException
         */
        public function startProcess(string $root = null)
        {
                if (is_null($root)) {
                        $root = $this->_root;
                }

                if (!isset($root)) {
                        $this->processDirectory(".");
                } else {
                        $this->processDirectory($root);
                }
        }

        /**
         * Process directory.
         * 
         * @param string $root The root directory.
         * @throws RuntimeException
         */
        private function processDirectory(string $root)
        {
                $this->setDirectory($root);
                $iterator = new DirectoryIterator($this->_root);

                foreach ($iterator as $fileinfo) {
                        $this->removeEntry($fileinfo);
                }
        }

        /**
         * Remove filesystem entry.
         * 
         * @param SplFileInfo $fileinfo The file info object.
         * @throws RuntimeException
         */
        private function removeEntry(SplFileInfo $fileinfo)
        {
                if ($fileinfo->getFilename() == "." ||
                    $fileinfo->getFilename() == "..") {
                        return;
                }
                if ($fileinfo->isDir()) {
                        $this->processDirectory($fileinfo->getPathname());
                        $this->deleteDirectory($fileinfo);
                }
                if ($fileinfo->isFile()) {
                        $this->deleteFile($fileinfo);
                }
        }

        /**
         * Remove directory.
         * 
         * @param SplFileInfo $fileinfo The file info object.
         * @throws RuntimeException
         */
        private function deleteDirectory(SplFileInfo $fileinfo)
        {
                if (!rmdir($fileinfo->getPathname())) {
                        throw new RuntimeException(sprintf("Failed delete directory %s in %s", $fileinfo->getBasename(), $this->_name));
                }
        }

        /**
         * Remove file.
         * 
         * @param SplFileInfo $fileinfo The file info object.
         * @throws RuntimeException
         */
        private function deleteFile(SplFileInfo $fileinfo)
        {
                if (!unlink($fileinfo->getPathname())) {
                        throw new RuntimeException(sprintf("Failed delete file %s in %s", $fileinfo->getBasename(), $this->_name));
                }
        }

}
