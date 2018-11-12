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

namespace Batchelor\Cache\Backend\Extension\ShmOp;

use RuntimeException;

/**
 * The IPC segment manager.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Manager
{

        /**
         * Options for shared memory segments.
         * @var array 
         */
        private $_options = [];
        /**
         * The shared memory repository.
         * @var array 
         */
        private $_handles = [];
        /**
         * Check if fetched segments are opened.
         * @var bool 
         */
        private $_checked = false;

        /**
         * Construtor.
         * @param array $options Options for shared memory segments.
         */
        public function __construct($options)
        {
                $this->_options = $options;
        }

        /**
         * Destructor.
         */
        public function __destruct()
        {
                foreach ($this->_handles as $handle) {
                        $handle->close();
                }
        }

        /**
         * Enable checked mode.
         * @param bool $enable Whether segment are checked if open.
         */
        public function setChecked($enable = true)
        {
                $this->_checked = $enable;
        }

        /**
         * Open shared memory segment.
         * @param string $key The segment name.
         * @param array $options Custom shared memory segments options.
         * @return Segment
         */
        public function create(string $key, array $options = []): Segment
        {
                $options = array_merge($this->_options, $options);
                $segment = new Segment($key, $options['flag'], $options['mode'], $options['size']);

                return $segment;
        }

        /**
         * Get shared memory segment from repository.
         * @param string $key The segment name.
         * @return Segment
         */
        public function get(string $key): Segment
        {
                return $this->_handles[$key];
        }

        /**
         * Add shared memory segment to repository.
         * @param string $key The segment name.
         * @param Segment $handle The shared memory segment.
         */
        public function add(string $key, Segment $handle)
        {
                $this->_handles[$key] = $handle;
        }

        /**
         * Remove shared memory segment from repository.
         * @param string $key The segment name.
         */
        public function remove(string $key)
        {
                unset($this->_handles[$key]);
        }

        /**
         * Check if repository contains shared memory segment.
         * @param string $key The segment name.
         * @return bool
         */
        public function has(string $key): bool
        {
                return isset($this->_handles[$key]);
        }

        /**
         * Fetch shared memory segment info repository.
         * 
         * This is an utility method that fetches existing segment from respository.
         * The segment will be created if missing and added to the repository. The
         * segment will be resized on request with optional copying of existing data
         * from the previous segment object.
         * 
         * @param string $key The segment name.
         * @param int $size The required segment size.
         * @return Segment
         */
        public function fetch(string $key, int $size = 0, bool $copy = false): Segment
        {
                if (!$this->has($key)) {
                        $segment = $this->create($key);
                        $this->add($key, $segment);
                } else {
                        $segment = $this->get($key);
                }

                if ($size != 0 && $size > $segment->getSize()) {
                        $segment->resize($size, $copy);
                }
                if ($this->_checked) {
                        if (!$segment->isOpen()) {
                                $segment->open();
                        }
                        if (!$segment->isOpen()) {
                                throw new RuntimeException("The shared memory segment is not open");
                        }
                }

                return $segment;
        }

}
