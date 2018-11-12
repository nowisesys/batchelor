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

use RuntimeException;
use SplFileInfo;

/**
 * The file class.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class File extends SplFileInfo
{

        /**
         * Constructor.
         * @param string $filename The filename.
         */
        public function __construct(string $filename)
        {
                parent::__construct($filename);
        }

        public function __toString()
        {
                return str_replace("//", "/", $this->getPathname());    // Fix '// ' -> '/'
        }

        /**
         * Get parent directory object.
         * 
         * This method has different behavior depending on if pathname is an
         * relative directory (the basename is ".."). In this case another ".." is
         * appended and used as the pathname for directory constructor. Otherwise
         * the dirname is used.
         * 
         * <code>
         * $file = new File("..");
         * $file->getParent();          // -> "../.."
         * 
         * $file = new File("..");
         * $file->getParent();          // -> "/tmp"
         * </code>
         * 
         * @return Directory
         */
        public function getParent(): Directory
        {
                if (($base = $this->getBasename()) == "..") {
                        return new Directory($this->getPathname() . "/..");
                } else {
                        return new Directory($this->getDirname());
                }
        }

        /**
         * Get directory from path.
         * 
         * This method has different behavior depending on if pathname refers to
         * an directory or file. The dirname is used for an file. For an directory
         * we simply uses the pathname.
         * 
         * @return Directory
         */
        public function getDirectory(): Directory
        {
                if ($this->isDir()) {
                        return new Directory($this->getPathname());
                } else {
                        return new Directory($this->getDirname());
                }
        }

        /**
         * Get path of parent directory.
         * 
         * This method strips the basename from this objects path. Returns an
         * empty string i.e. if "." was used as path.
         * 
         * @return string
         */
        public function getDirname(): string
        {
                $name = $this->getPathname();
                $base = $this->getBasename();
                $size = strlen($base);
                $path = rtrim(substr($name, 0, -$size), '/');

                if (!$path && $name[0] == '/') {
                        $path = "/";
                }

                return $path;
        }

        /**
         * Get file content.
         * @return string
         */
        public function getContent(): string
        {
                return file_get_contents($this->getPathname());
        }

        /**
         * Put file content.
         * 
         * @param mixed $data The content to write.
         * @param int $flags Optional flags (i.e. FILE_APPEND or LOCK_EX).
         * @return int Number of bytes written or false on failure.
         */
        public function putContent($data, $flags = 0)
        {
                return file_put_contents($this->getPathname(), $data, $flags);
        }

        /**
         * Send file to stdout.
         * 
         * <code>
         * $file = new File("indata.txt");
         * $file->sendFile();           // Send for download.
         * $file->sendFile(false);      // No download headers.
         * $file->sendFile(true, [      // Use custom headers.
         *      'X-Batchelor' => 'The user manual'
         * ]);
         * </code>
         * 
         * @param bool $standard Send standard headers.
         * @param array $headers Optional HTTP headers.
         */
        public function sendFile($standard = true, $headers = [])
        {
                if ($standard) {
                        $stdhead = $this->getDownloadHeaders();
                        $headers = array_merge($stdhead, $headers);
                }

                foreach ($headers as $key => $val) {
                        header("$key: $val");
                }

                readfile($this->getRealPath());
        }

        /**
         * Get MIME type.
         * 
         * @return string
         */
        public function getMimeType(): string
        {
                if (($mime = $this->getMimeMagic())) {
                        return $mime;
                } else {
                        return 'application/octet-stream';
                }
        }

        /**
         * Get MIME type from magic database.
         * @return string
         */
        private function getMimeMagic(): string
        {
                if (!function_exists("mime_content_type")) {
                        return false;
                } else {
                        return mime_content_type($this->getRealPath());
                }
        }

        /**
         * Get HTTP download headers.
         * @return array
         */
        public function getDownloadHeaders()
        {
                return [
                        'Content-Disposition' => sprintf('attachment; filename="%s"', $this->getBasename()),
                        'Content-Type'        => $this->getMimeType(),
                        'Content-Length'      => $this->getSize(),
                        'ETag'                => md5($this->getMTime())
                ];
        }

        /**
         * Delete this file.
         */
        public function delete()
        {
                if (!file_exists($this->getRealPath())) {
                        return;
                } elseif (!is_file($this->getRealPath())) {
                        throw new RuntimeException("The references path is not an file");
                } elseif (!unlink($this->getRealPath())) {
                        throw new RuntimeException("Failed delete this file");
                }
        }

        /**
         * Get source code language.
         * 
         * Detect coding language from file extension. Returns empty for plain
         * text files and file extension for unmapped alternatives.
         * 
         * @return string 
         */
        public function getLanguage(): string
        {
                switch ($this->getExtension()) {
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
                                return $this->getExtension();
                }
        }

}
