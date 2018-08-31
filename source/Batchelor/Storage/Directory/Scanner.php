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

use Batchelor\Storage\Directory\Iterator\Filter\ArrayFilter;
use Batchelor\Storage\Directory\Iterator\Filter\FiletypeFilter;
use Batchelor\Storage\Directory\Iterator\Filter\RegexFilter;
use Batchelor\Storage\Directory\Iterator\Format\ScannerFormat;
use DirectoryIterator;
use IteratorAggregate;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * The directory scanner.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Scanner implements IteratorAggregate
{

        /**
         * Skip dot files.
         */
        const SKIP_DOTS = 1;
        /**
         * Skip files.
         */
        const SKIP_FILES = 2;
        /**
         * Skip directories.
         */
        const SKIP_DIRS = 4;
        /**
         * Skip hidden files.
         */
        const SKIP_HIDDEN = 8;
        /**
         * Skip symbolic links.
         */
        const SKIP_LINKS = 16;
        /**
         * Skip empty files (0 bytes size).
         */
        const SKIP_EMPTY = 32;
        /**
         * Get filenames relative current directory.
         */
        const FILENAME_RELATIVE = 1;
        /**
         * Get filenames anchored at root path.
         */
        const FILENAME_ANCHORED = 2;
        /**
         * Get filenames using real path.
         */
        const FILENAME_REALPATH = 3;
        /**
         * Get filename by casting fileinfo to string.
         */
        const FILENAME_STR_CAST = 4;

        /**
         * The root directory.
         * @var string 
         */
        private $_root;
        /**
         * Explore sub directories.
         * @var bool
         */
        private $_recursive = false;
        /**
         * The filename filter (regex or filenames).
         * @var string|array 
         */
        private $_filter = false;
        /**
         * The output format.
         * @var int 
         */
        private $_format = self::FILENAME_RELATIVE;
        /**
         * Bitmask of FiletypeFilterIterator::SKIP_XXX constants.
         * @var int 
         */
        private $_options = 0;

        /**
         * Constructor.
         * @param string $root The root directory.
         * @param array $options Optional options (i.e. filter).
         */
        public function __construct($root, $options = [])
        {
                $this->_root = $root;

                if (isset($options['recursive'])) {
                        $this->_recursive = $options['recursive'];
                }
                if (isset($options['filter'])) {
                        $this->_filter = $options['filter'];
                }
                if (isset($options['options'])) {
                        $this->_options = $options['options'];
                }
        }

        /**
         * Get root path.
         * @return string
         */
        public function getPath()
        {
                return $this->_root;
        }

        /**
         * Set scan options.
         * 
         * @param int|array $options Optional scanner settings.
         */
        public function setOptions($options)
        {
                if (is_int($options)) {
                        $options = [
                                'options' => $options
                        ];
                }

                if (isset($options['options'])) {
                        $this->_options = $options['options'];
                }
                if (isset($options['filter'])) {
                        $this->_filter = $options['filter'];
                }
                if (isset($options['format'])) {
                        $this->_format = $options['format'];
                }
        }

        /**
         * Enable recursive scanning.
         * @param bool $enable The recursive mode.
         */
        public function setRecursive($enable = true)
        {
                $this->_recursive = $enable;
        }

        /**
         * Set scan filter.
         * 
         * The filter is either an string contaning a regex or an array containing
         * accepted filenames. Pass false to disable filtering.
         * 
         * @param string|array $filter The filter to use.
         */
        public function setFilter($filter)
        {
                $this->_filter = $filter;
        }

        /**
         * Set output format.
         * @param int $format The output format (one of the FILENAME_XXX constants). 
         */
        public function setFormat($format)
        {
                $this->_format = $format;
        }

        /**
         * Get files list.
         * 
         * Returns array containing files matching currently defined options.
         * 
         * <code>
         * // 
         * // Using scalar options:
         * // 
         * $files = $scanner->getFiles(Scanner::SKIP_DOTS);
         * 
         * // 
         * // Using array of options:
         * // 
         * $files = $scanner->getFiles([
         *      'options' => Scanner::SKIP_DOTS, 
         *      'format'  => Scanner::FILENAME_REALPATH
         * ]);
         * </code>
         * 
         * @param int|array $options Optional scanner settings.
         * @return string[]
         */
        public function getFiles($options = false, $result = [])
        {
                $this->setFiles($result, $options);
                return $result;
        }

        /**
         * Set files list.
         * 
         * @param array $result The output array.
         * @param int|array $options Optional scanner settings.
         * @see Scanner::getFiles() For options description.
         */
        public function setFiles(&$result, $options = false)
        {
                if ($options) {
                        $this->setOptions($options);
                }

                if (($formatter = $this->getResult())) {
                        foreach ($formatter as $file) {
                                $result[] = $file;
                        }
                }
        }

        /**
         * Get result iterator.
         * @return ScannerFormat
         */
        public function getResult()
        {
                return new ScannerFormat($this, $this->_format);
        }

        /**
         * Get directory iterator.
         * @return Iterator
         */
        public function getIterator()
        {
                if ($this->_recursive) {
                        $iterator = new RecursiveDirectoryIterator($this->_root);
                        $iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
                } else {
                        $iterator = new DirectoryIterator($this->_root);
                }

                if (is_string($this->_filter)) {
                        $iterator = new RegexFilter($iterator, $this->_filter);
                }
                if (is_array($this->_filter)) {
                        $iterator = new ArrayFilter($iterator, $this->_filter);
                }
                if ($this->_options) {
                        $iterator = new FiletypeFilter($iterator, $this->_options);
                }

                return $iterator;
        }

}
