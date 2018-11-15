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

namespace Batchelor\WebService\Types;

use Batchelor\Storage\File as StorageFile;

/**
 * The file class.
 * 
 * Represent a file located inside an work queue. The name is relative to the 
 * directory containing the work queue. The lang is only applicable on source
 * code files.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class File
{

        /**
         * The file name.
         * @var string 
         */
        public $name;
        /**
         * The file size in bytes.
         * @var int 
         */
        public $size;
        /**
         * The MIME type.
         * @var string 
         */
        public $mime;
        /**
         * The file type (dir, file or link).
         * @var string 
         */
        public $type;
        /**
         * The source code language.
         * @var string 
         */
        public $lang;

        /**
         * Constructor.
         * 
         * @param string $name The file name.
         * @param int $size The file size in bytes.
         * @param string $mime The MIME type.
         * @param string $type The file type (i.e. dir, file or link)
         * @param string $lang The source code language.
         */
        public function __construct(string $name, int $size, string $mime, string $type = 'file', string $lang = 'text')
        {
                $this->name = $name;
                $this->size = $size;
                $this->mime = $mime;
                $this->type = $type;
                $this->lang = $lang;
        }

        /**
         * The factory function.
         * 
         * @param string $name The relative filename.
         * @param StorageFile $file The storage file.
         * @return File 
         */
        public static function create(string $name, StorageFile $file): self
        {
                return new self($name, $file->getSize(), $file->getMimeType(), $file->getType(), $file->getLanguage());
        }

}
