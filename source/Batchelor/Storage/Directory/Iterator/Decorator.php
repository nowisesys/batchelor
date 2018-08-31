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

namespace Batchelor\Storage\Directory\Iterator;

use Batchelor\Storage\Directory;
use Batchelor\Storage\File;
use RecursiveDirectoryIterator;

/**
 * Decorator for recursive directory iterator.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Decorator extends RecursiveDirectoryIterator
{

        public function __construct(string $path, int $flags)
        {
                parent::__construct($path, $flags);
                parent::setInfoClass(File::class);
        }

        /**
         * Get directory object.
         * @return Directory
         */
        public function getDirectory()
        {
                return new Directory($this->getPath(), $this->getFlags());
        }

}
