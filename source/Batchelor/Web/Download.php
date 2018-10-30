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

namespace Batchelor\Web;

use RuntimeException;

/**
 * The content downloader.
 * 
 * <code>
 * // 
 * // Download all content at once:
 * // 
 * $content = $download->getContent();
 * 
 * // 
 * // Open stream first when using stream methods:
 * // 
 * $download->getStream();
 * 
 * // 
 * // Download content in chunks:
 * // 
 * while (($buff = $download->getChunk())) { ... }
 * 
 * // 
 * // Check good status on stream in loop:
 * // 
 * while ($download->isGood()) {
 *      $buff = $download->getChunk();
 * }
 * </code>
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Download
{

        /**
         * Default chunk size is 4 MB.
         */
        const CHUNK_SIZE = 4194304;
        /**
         * Default maximum line lenght.
         */
        const LINE_LENGTH = 4096;

        /**
         * The download URL.
         * @var string 
         */
        private $_url;
        /**
         * The download resource.
         * @var resource 
         */
        private $_handle;
        /**
         * The chunk size.
         * @var int 
         */
        private $_size;

        /**
         * Constructor.
         * 
         * @param string $url The download URL.
         * @param int $size The size of each chunk to read.
         */
        public function __construct(string $url, int $size = self::CHUNK_SIZE)
        {
                $this->_url = $url;
                $this->_size = $size;
        }

        /**
         * Destructor.
         */
        public function __destruct()
        {
                self::close($this->_handle);
        }

        /**
         * Set chunk size for download.
         * @param int $size The chunk size.
         */
        public function setSize(int $size)
        {
                $this->_size = $size;
        }

        /**
         * Get URL content.
         * 
         * This method downloads the complete content from the URL. Notice that 
         * an fatal "out of memory" error will be triggered if the URL points to 
         * an large web resource.
         * 
         * @return string The downloaded data.
         * @throws RuntimeException
         */
        public function getContent(): string
        {
                $handle = $this->getStream();
                $result = "";

                try {
                        while (!feof($handle)) {
                                if (!($buff = fread($handle, $this->_size))) {
                                        throw new RuntimeException(sprintf(
                                            "Failed read %d bytes from %s", $this->_size, $this->_url
                                        ));
                                } else {
                                        $result .= $buff;
                                }
                        }
                } finally {
                        self::close($handle);
                }

                return $result;
        }

        /**
         * Get URL stream.
         * 
         * @return resource
         * @throws RuntimeException
         */
        public function getStream()
        {
                return $this->_handle = self::open($this->_url, $this->_size);
        }

        /**
         * Get chunk from URL.
         * 
         * This method can be called to download a chunk of data from the URL.
         * A previous successful call to getStream() has to be done before 
         * calling this method.
         * 
         * @param int $size The chunk size.
         * @return string 
         */
        public function getChunk(int $size = self::CHUNK_SIZE): string
        {
                if (!is_resource($this->_handle)) {
                        throw new RuntimeException("Please open the URL stream first");
                }
                if (feof($this->_handle)) {
                        return "";
                }

                if (!($buff = fread($this->_handle, $size))) {
                        throw new RuntimeException(sprintf(
                            "Failed read %d bytes from %s", $size, $this->_url
                        ));
                }

                return $buff;
        }

        public function getLine(int $length = self::LINE_LENGTH, bool $strip = false, string $tags = null): string
        {
                if (!is_resource($this->_handle)) {
                        throw new RuntimeException("Please open the URL stream first");
                }
                if (feof($this->_handle)) {
                        return "";
                }

                if ($strip) {
                        $buff = fgetss($this->_handle, $length, $tags);
                } else {
                        $buff = fgets($this->_handle, $length);
                }

                if ($buff === false) {
                        throw new RuntimeException(sprintf(
                            "Failed read %d bytes from %s", $length, $this->_url
                        ));
                }

                return $buff;
        }

        /**
         * Set stream position.
         * 
         * Return true if successful, otherwise false.
         * 
         * @param int $offset The offset value.
         * @param int $whence The seek value.
         * @return bool
         */
        public function setPosition(int $offset, int $whence = SEEK_SET): bool
        {
                if (!is_resource($this->_handle)) {
                        throw new RuntimeException("Please open the URL stream first");
                } else {
                        return fseek($this->_handle, $offset, $whence) === 0;
                }
        }

        /**
         * Check if stream is good.
         * 
         * The stream is good if it's an opened stream and not at end of file.
         * @return bool 
         */
        public function isGood(): bool
        {
                if (!is_resource($this->_handle)) {
                        return false;
                } else {
                        return feof($this->_handle) === false;
                }
        }

        /**
         * Open resource stream.
         * 
         * @param string $url The download URL.
         * @param int $size The chunk size.
         * @return resource
         * @throws RuntimeException
         */
        private static function open(string $url, int $size)
        {
                if (ini_get("allow_url_fopen") != 1) {
                        throw new RuntimeException("The allow_url_fopen setting is not enabled");
                }

                if (!($stream = fopen($url, "r"))) {
                        throw new RuntimeException("Failed open URL $url");
                }
                if (stream_set_chunk_size($stream, $size) == false) {
                        throw new RuntimeException("Failed set chunk size on stream");
                }
                if (stream_set_read_buffer($stream, $size) != 0) {
                        throw new RuntimeException("Failed set read buffer size on stream");
                }

                return $stream;
        }

        /**
         * Close resource handle.
         * 
         * @param resource $handle The resource handle.
         * @throws RuntimeException
         */
        private static function close($handle)
        {
                if (!is_resource($handle)) {
                        return;
                }
                if (!fclose($handle)) {
                        throw new RuntimeException("Failed close resource handle");
                }
        }

}
