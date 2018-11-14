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

use Batchelor\Storage\Directory\Scanner;
use RuntimeException;
use ZipArchive;

/**
 * ZIP archive creator.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Archive
{

        /**
         * The archive name.
         * @var string 
         */
        private $_zipfile;
        /**
         * The ZIP archive.
         * @var ZipArchive 
         */
        private $_archive;

        /**
         * Constructor.
         * 
         * @param string $zipfile The archive name.
         */
        public function __construct(string $zipfile, int $flags = ZipArchive::CREATE)
        {
                if (!extension_loaded("zip")) {
                        throw new RuntimeException("The zip extension is not loaded");
                }
                if (file_exists($zipfile)) {
                        $flags = ZipArchive::OVERWRITE;
                }

                $this->_zipfile = $zipfile;
                $this->_archive = self::create($zipfile, $flags);
        }

        /**
         * Get ZIP archive.
         * @return ZipArchive
         */
        public function getArchive(): ZipArchive
        {
                return $this->_archive;
        }

        /**
         * Get ZIP filename.
         * @return string
         */
        public function getFilename(): string
        {
                return $this->_zipfile;
        }

        /**
         * Add directory to archive.
         * @param Directory $directory The directory to add.
         */
        public function addDirectory(Directory $directory)
        {
                foreach ($directory->scan(
                    Scanner::SKIP_DOTS | Scanner::SKIP_DIRS |
                    Scanner::SKIP_THIS | Scanner::SKIP_LINKS
                ) as $filename) {
                        $file = $directory->getFile($filename);
                        $this->_archive->addFile($file->getRealPath(), $filename);
                }
        }

        /**
         * Add file to archive.
         * @param File $file The file to add.
         */
        public function addFile(File $file)
        {
                $this->_archive->addFile($file->getRealPath(), $file->getPathname());
        }

        /**
         * Close archive.
         */
        public function close()
        {
                $this->_archive->close();
        }

        /**
         * Create ZIP archive.
         * 
         * @param string $zipfile The ZIP file.
         * @param int $flags The open flags.
         * @return ZipArchive
         */
        private static function create(string $zipfile, int $flags): ZipArchive
        {
                $archive = new ZipArchive();
                $archive->open($zipfile, $flags);
                $archive->setArchiveComment("Created by batchelor");

                return $archive;
        }

}
