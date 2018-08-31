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
use Batchelor\System\Directory\Iterator\Decorator as RecursiveDirectoryIterator;
use Batchelor\System\Directory\Scanner;
use FilesystemIterator;
use InvalidArgumentException;
use IteratorAggregate;
use RuntimeException;
use SplFileInfo;

/**
 * The directory class.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Directory implements IteratorAggregate
{

        /**
         * The path info.
         * @var File 
         */
        private $_finfo;
        /**
         * Flags for iterator,
         * @var int
         */
        private $_flags;

        /**
         * Directory iterator seems to have "odd" behavior:
         * 
         *      $directory = new DirectoryIterator(__DIR__);
         *      $directory->getRealPath());  // Not returning __DIR__!!
         * 
         * One would expect current() to be used for accessing current pointed file, 
         * while other methods returned data for the original path. It seems that 
         * the directory is always scanned.
         */

        /**
         * Constructor.
         * 
         * <code>
         * $directory = new Directory();                        // Use root directory.
         * $directory = new Directory("/var/data/jobs");        // Use this directory.
         * $directory = new Directory(getcwd());                // Use current directory.
         * 
         * // 
         * // Pass optional flags as second argument. These are bitwise merged with the 
         * // default flags like open current as self.
         * // 
         * $discovery = FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS;
         * $directory = new Directory("/var/data/jobs", $discovery);
         * </code>
         * 
         * @param string $path The directory path.
         * @param int $flags Flags passed to recursive iterator.
         */
        public function __construct(string $path = "/", $flags = FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::SKIP_DOTS)
        {
                $this->_finfo = new SplFileInfo($path);
                $this->_flags = $flags;
        }

        public function __toString()
        {
                return $this->_finfo->getPathname();
        }

        /**
         * Get directory iterator.
         * @return RecursiveDirectoryIterator
         */
        public function getIterator(): RecursiveDirectoryIterator
        {
                return new RecursiveDirectoryIterator($this->_finfo->getPathname(), $this->_flags);
        }

        /**
         * Get path info object.
         * @return SplFileInfo
         */
        public function getPathInfo()
        {
                return $this->_finfo;
        }

        /**
         * Get the path used to contruct this object.
         * @return string
         */
        public function getPathname()
        {
                return $this->_finfo->getPathname();
        }

        /**
         * The basename unless being a root directory.
         * @return string
         */
        public function getFilename()
        {
                return $this->_finfo->getFilename();
        }

        /**
         * Get parent path of this directory.
         * 
         * Might be empty if this directory path is "." or ".." or an
         * directory immediate under the root directory.
         * 
         * @return string
         */
        public function getPath()
        {
                return $this->_finfo->getPath();
        }

        /**
         * Get basename for this directory.
         * @param string $suffix Strip suffix from name.
         * @return string
         */
        public function getBasename($suffix = null)
        {
                return $this->_finfo->getBasename($suffix);
        }

        /**
         * Get path of parent directory.
         * 
         * This method strips the basename from this objects path. Returns an
         * empty string i.e. if "." was used as path.
         * 
         * @return string
         */
        public function getDirname()
        {
                return (new File($this->getPathname()))->getDirname();
        }

        /**
         * Get real path of this directory.
         * 
         * @return string
         */
        public function getRealPath()
        {
                return $this->_finfo->getRealPath();
        }

        /**
         * Get sub directory path.
         * 
         * @param string $path The sub directory.
         */
        public function getSubPath($path)
        {
                return sprintf("%s/%s", $this->getPathname(), $path);
        }

        /**
         * Get parent directory.
         * @return Directory The parent directory.
         */
        public function getParent(): Directory
        {
                return (new File($this->getPathname()))->getParent();
        }

        /**
         * Open file.
         * 
         * The filename is either an absolute path or relative to this directory
         * object path.
         * 
         * @param string $filename The filename,
         * @return File
         */
        public function getFile(string $filename): File
        {
                if (!isset($filename)) {
                        throw new InvalidArgumentException("Missing filename");
                } elseif ($filename[0] != '/') {
                        return new File($this->getSubPath($filename));
                } else {
                        return new File($filename);
                }
        }

        /**
         * Check if directory exists.
         * 
         * The default is to check if this object directory exists. An relative 
         * path is checked as sub directory of this object.
         * 
         * <code>
         * $directory->exists();                // Check if this directory exist.
         * $directory->exists("subdir");        // Check if sub directory exist.
         * $directory->exists("/tmp/subdir");   // Check using absolute path.
         * </code>
         * 
         * @param string $path The directory path.
         * @return bool
         */
        public function exists(string $path = null): bool
        {
                if (!isset($path)) {
                        return file_exists($this->getRealPath());
                } elseif ($path[0] != '/') {
                        return file_exists($this->getSubPath($path));
                } else {
                        return file_exists($path);
                }
        }

        /**
         * Create directory.
         * 
         * This method creates the directory and returns a new directory object for
         * the created directory. The default is to create a directory pointed to by
         * this object. Relative pathes are treated as an sub directory of this object
         * directory.
         * 
         * <code>
         * $directory->create();                // Create this directory.
         * $directory->create("subdir");        // Create sub directory.
         * $directory->create("/tmp/subdir");   // Create using absolute path.
         * </code>
         * 
         * Trying to create an existing directory will simply return an directory
         * object for it.
         * 
         * @param string $path The directory path.
         * @param int $mode The directory permission.
         * @return Directory The created directory object.
         * @throws RuntimeException
         */
        public function create(string $path = null, int $mode = 0755)
        {
                if (!isset($path)) {
                        $path = $this->getPathname();
                } elseif ($path[0] != '/') {
                        $path = $this->getSubPath($path);
                }

                if (file_exists($path)) {
                        return new Directory($path);
                } else if (!mkdir($path, $mode, true)) {
                        throw new RuntimeException(sprintf("Failed create directory %s", basename($path)));
                } else {
                        return new Directory($path);
                }
        }

        /**
         * Open directory.
         * 
         * This method is the companion of create() except that it will not create
         * missing directory. An exception will be throwed if directory is missing
         * or not readable and writable.
         * 
         * @param string $path The directory path.
         * @param bool $readable Check that directory is readble.
         * @param bool $writable Check that directory is writable.
         * @return Directory The created directory object.
         * @throws RuntimeException
         */
        public function open(string $path = null, $readable = true, $writable = true)
        {
                if (!isset($path)) {
                        $path = $this->getPathname();
                } elseif ($path[0] != '/') {
                        $path = $this->getSubPath($path);
                }

                if (!isset($path)) {
                        throw new RuntimeException("The directory path is empty");
                } elseif (!file_exists($path)) {
                        throw new RuntimeException("The directory is missing");
                } elseif (!is_dir($path)) {
                        throw new RuntimeException("The path is not an directory");
                } elseif ($readable && !is_readable($path)) {
                        throw new RuntimeException("The directory is not readable");
                } elseif ($writable && !is_writable($path)) {
                        throw new RuntimeException("The directory is not writable");
                } else {
                        return new Directory($path);
                }
        }

        /**
         * Delete directory.
         * 
         * Call this method to delete directory. The default is to delete the
         * directory pointed to by this object. When deleting this object, consider 
         * the current directory object as invalid. If path is relative then its
         * treated as a sub directory of this directory object.
         * 
         * <code>
         * $directory->delete();                // Delete this directory.
         * $directory->delete("subdir");        // Delete sub directory.
         * $directory->delete("/tmp/subdir");   // Delete using absolute path.
         * </code>
         * 
         * @param string $path The directory to delete.
         * @param bool $recursive Work recursive on directory content.
         * @throws RuntimeException
         */
        public function delete(string $path = null, bool $recursive = true)
        {
                if (!isset($path)) {
                        $path = $this->getRealPath();
                } elseif ($path[0] != '/') {
                        $path = $this->getSubPath($path);
                }
                if (!isset($path)) {
                        throw new RuntimeException("The directory path is empty");
                }
                if ($recursive) {
                        $this->cleanup($path);
                }

                if (!rmdir($path)) {
                        throw new RuntimeException(sprintf("Failed delete directory %s", basename($path)));
                }
        }

        /**
         * Cleanup inside directory.
         * 
         * Deletes content (files and directories) in a directory, but leaves the
         * directory itself. This is useful for removing temporary files from a
         * working directory when a task completes. If path is relative then its
         * treated as a sub directory of this directory object.
         * 
         * <code>
         * $directory->cleanup();                // Cleanup this directory.
         * $directory->cleanup("subdir");        // Cleanup sub directory.
         * $directory->cleanup("/tmp/subdir");   // Cleanup using absolute path.
         * </code>
         * 
         * This method is equivalent with the delete method except for keeping
         * the given directory itself.
         * 
         * @param string $path The directory to cleanup.
         * @throws RuntimeException
         */
        public function cleanup(string $path = null)
        {
                if (!isset($path)) {
                        $path = $this->getRealPath();
                } elseif ($path[0] != '/') {
                        $path = $this->getSubPath($path);
                }
                if (!isset($path)) {
                        throw new RuntimeException("The directory path is empty");
                } else {
                        $this->remove($path);
                }
        }

        /**
         * Directory cleanup helper.
         * 
         * @param string $root The root path.
         * @throws RuntimeException
         */
        private function remove(string $root)
        {
                $cleanup = new Cleanup($root);
                $cleanup->startProcess();
        }

        /**
         * Read files and folders.
         * 
         * This is another frontend for the directory scanner.
         * 
         * @param bool $files Include files in listing.
         * @param bool $dirs Include directories in listing.
         * @return string[] The list of files.
         */
        public function read($files = true, $dirs = true)
        {
                $options = Scanner::SKIP_DOTS;

                if (!$files) {
                        $options |= Scanner::SKIP_FILES;
                }
                if (!$dirs) {
                        $options |= Scanner::SKIP_DIRS;
                }

                return $this->scan($options);
        }

        /**
         * Read files in directory.
         * 
         * Return file listing of current directory. For more flexibility, use the
         * directory scanner class. The default is to skip dots files and return a
         * list with filenames anchored at this directory path.
         * 
         * @param int $options Skip files options (zero or more Scanner::SKIP_XXX constants).
         * @param int $format The filename format (one of Scanner::FILENAME_XXX).
         * @return string[] The list of files.
         */
        public function scan($options = Scanner::SKIP_DOTS, $format = Scanner::FILENAME_ANCHORED)
        {
                $scanner = new Scanner($this->getPathname());
                $scanner->setRecursive();
                $scanner->setOptions($options);
                $scanner->setFormat($format);

                return $scanner->getFiles();
        }

}
