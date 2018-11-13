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

namespace Batchelor\Storage\File;

use Batchelor\Storage\File;

/**
 * Source code language.
 * 
 * Use this class for detecting the source code language of an file. Primarly
 * useful for syntax highlight of file content. Obviously, most files are not
 * source code files at all, for these we return i.e. text to denote a plain 
 * text file.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Source
{

        /**
         * The source code language.
         * @var string 
         */
        private $_lang = "";

        /**
         * Constructor.
         * @param File $file The source file.
         */
        public function __construct(File $file)
        {
                $this->setLanguage($file);
        }

        /**
         * Get source code language.
         * @return string
         */
        public function getLanguage(): string
        {
                return $this->_lang;
        }

        /**
         * Set source code language.
         * @param File $file The source file.
         */
        private function setLanguage(File $file)
        {
                if (($lang = $this->getFromExtension($file))) {
                        $this->_lang = $lang;
                        return;
                }
                if (($lang = $this->getFromMimeType($file))) {
                        $this->_lang = $lang;
                        return;
                }
        }

        /**
         * Use file extension for detection.
         * 
         * @param File $file The source file.
         * @return string
         */
        private function getFromExtension(File $file): string
        {
                switch ($file->getExtension()) {
                        case 'html':
                                return "html";
                        case 'css':
                                return "css";
                        case 'js':
                                return "javascript";
                        case 'php':
                        case 'inc':
                                return "php";
                        case 'c++':
                        case 'cpp':
                        case 'cxx':
                        case 'h++':
                        case 'hpp':
                        case 'hxx':
                                return "c++";
                        case 'c':
                        case 'C':
                        case 'h':
                                return "c";
                        case 'cs':
                                return "cs";
                        case 'java':
                                return "java";
                        case 'py':
                                return "python";
                        case 'pl':
                                return "perl";
                        case 'bash':
                        case 'sh':
                                return "bash";
                        case 'csh':
                                return "bash";
                        case 'tcsh':
                                return "bash";
                        case 'diff':
                        case 'patch':
                                return "diff";
                        case 'cron':
                                return "bash";
                        case 'txt':
                        case 'text':
                                return "text";
                        case 'ascii':
                        case 'asciidoc':
                                return "asciidoc";
                        default:
                                return $file->getExtension();
                }
        }

        /**
         * Use MIME type for detection.
         * 
         * @param File $file The source file.
         * @return string
         */
        private function getFromMimeType(File $file): string
        {
                switch ($file->getMimeType()) {
                        case "text/plain":
                                return "text";
                        default:
                                return "";
                }
        }

}
