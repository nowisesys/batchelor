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

namespace Batchelor\Storage\Directory\Iterator\Filter;

use Batchelor\Storage\Directory\Scanner;
use FilterIterator;
use Iterator;
use SplFileInfo;

/**
 * Filetype filtering of iterator.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class FiletypeFilter extends FilterIterator
{

        /**
         * The Scanner::SKIP_XXX bitmask.
         * @var int
         */
        private $_filter;

        /**
         * Constructor.
         * 
         * @param Iterator $iterator The inner iterator.
         * @param int $filter The bitmask filter (Scanner::SKIP_XXX).
         */
        public function __construct($iterator, int $filter)
        {
                parent::__construct($iterator);
                $this->_filter = $filter;
        }

        /**
         * Check if filter match.
         * @return bool
         */
        public function accept(): bool
        {
                return $this->filter($this->current(), $this->_filter);
        }

        /**
         * Check file against filter.
         * 
         * @param SplFileInfo $fileinfo File info for current file.
         * @param int $filter The filter options.
         * @return boolean
         */
        private function filter(SplFileInfo $fileinfo, int $filter)
        {
                $filename = $fileinfo->getFilename();

                if (($filter & Scanner::SKIP_DOTS) && ($filename == "." || $filename == "..")) {
                        return false;
                }
                if (($filter & Scanner::SKIP_HIDDEN) && $filename[0] == ".") {
                        return false;
                }
                if (($filter & Scanner::SKIP_DIRS) && $fileinfo->isDir()) {
                        return false;
                }
                if (($filter & Scanner::SKIP_FILES) && $fileinfo->isFile()) {
                        return false;
                }
                if (($filter & Scanner::SKIP_LINKS) && $fileinfo->isLink()) {
                        return false;
                }
                if (($filter & Scanner::SKIP_EMPTY) && $fileinfo->getSize() == 0) {
                        return false;
                }
                if (($filter & Scanner::SKIP_THIS) && empty($filename)) {
                        return false;
                }

                return true;
        }

}
