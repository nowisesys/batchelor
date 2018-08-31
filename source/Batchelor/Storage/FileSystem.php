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

namespace Batchelor\Storage;

use InvalidArgumentException;

/**
 * The system filesystem.
 * 
 * This class represent the complete filesystem on the server. Passing an empty 
 * path as argument for the delete() or cleanup() memthods will throw an invalid 
 * argument exception.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class FileSystem extends Directory
{

        /**
         * Constructor.
         */
        public function __construct()
        {
                parent::__construct("/");
        }

        /**
         * {@inheritdoc}
         */
        public function create(string $path = null, int $mode = 0755): Directory
        {
                return parent::create($this->getNormalized($path), $mode);
        }

        /**
         * {@inheritdoc}
         */
        public function cleanup(string $path = null)
        {
                if (empty($path)) {
                        throw new InvalidArgumentException("The directory path can't be empty");
                } else {
                        parent::cleanup($this->getNormalized($path));
                }
        }

        /**
         * {@inheritdoc}
         */
        public function delete(string $path = null, bool $recursive = true)
        {
                if (empty($path)) {
                        throw new InvalidArgumentException("The directory path can't be empty");
                } else {
                        parent::delete($this->getNormalized($path), $recursive);
                }
        }

        /**
         * {@inheritdoc}
         */
        public function exists(string $path = null): bool
        {
                return parent::exists($this->getNormalized($path));
        }

        /**
         * {@inheritdoc}
         */
        public function open(string $path = null, $readable = true, $writable = true): Directory
        {
                return parent::open($this->getNormalized($path), $readable, $writable);
        }

        /**
         * Get normalized path.
         * 
         * <code>
         * $filesystem->getNormalized("tmp");   // -> "/tmp"
         * $filesystem->getNormalized("//tmp");   // -> "/tmp"
         * </code>
         * 
         * @param string $path The input path
         * @return string
         */
        private function getNormalized(string $path = null): string
        {
                return str_replace("//", "/", sprintf("%s%s", $this->getPathname(), $path));
        }

}
