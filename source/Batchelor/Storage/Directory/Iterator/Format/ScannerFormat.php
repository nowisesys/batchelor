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

namespace Batchelor\Storage\Directory\Iterator\Format;

use Batchelor\Storage\Directory\Scanner;
use IteratorAggregate;

/**
 * Foramt directory scanner.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class ScannerFormat extends GenericFormat implements IteratorAggregate
{

        /**
         * Constructor.
         * @param Scanner $scanner The scanner object.
         * @param int $format The output format.
         */
        public function __construct($scanner, $format)
        {
                parent::__construct($scanner);
                parent::setCallback($this->getFormatter($scanner, $format));
        }

        /**
         * Get fileinfo formatter.
         * 
         * @param Scanner $scanner The scanner object.
         * @param int $format The output format.
         * @return callable
         */
        private function getFormatter($scanner, $format)
        {
                $size = strlen(realpath($scanner->getPath())) + 1;

                switch ($format) {
                        case Scanner::FILENAME_ANCHORED:
                                return function($fileinfo) use($size) {
                                        return substr($fileinfo->getRealpath(), $size);
                                };
                        case Scanner::FILENAME_REALPATH:
                                return function($fileinfo) {
                                        return $fileinfo->getRealpath();
                                };
                        case Scanner::FILENAME_RELATIVE:
                                return function($fileinfo) {
                                        return sprintf("%s/%s", $fileinfo->getPath(), $fileinfo->getFilename());
                                };
                        case Scanner::FILENAME_STR_CAST:
                                return function($fileinfo) {
                                        return (string) $fileinfo;
                                };
                }
        }

}
