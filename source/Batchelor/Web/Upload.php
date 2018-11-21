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

use BadMethodCallException;
use RuntimeException;

/**
 * Handle file upload.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Upload
{

        /**
         * The file upload.
         * @var array 
         */
        private $_data;

        /**
         * Constructor.
         * @param string $name
         */
        public function __construct(string $name = 'file')
        {
                if (!isset($_FILES[$name])) {
                        throw new BadMethodCallException("Missing file post");
                }
                if (!isset($_FILES[$name]['tmp_name'])) {
                        throw new BadMethodCallException("Missing source file (temp)");
                }
                if (!is_uploaded_file($_FILES[$name]['tmp_name'])) {
                        throw new BadMethodCallException("Temp file is not referencing an uploaded file");
                }
                if ($_FILES[$name]['size'] == 0) {
                        throw new BadMethodCallException("Uploaded file is empty");
                }
                if ($_FILES[$name]['error'] != 0) {
                        throw new BadMethodCallException("Error condition in file upload");
                }

                $this->setSubmission($_FILES[$name]);
        }

        /**
         * Set upload data.
         * @param array $data The upload data.
         */
        private function setSubmission(array $data)
        {
                $this->_data = $data;

                $from = $data['tmp_name'];
                $path = sprintf("%s/%s", dirname($from), md5(microtime()));

                $this->setFilepath($from, $path);
        }

        /**
         * Get submitted data.
         * @return array
         */
        public function getSubmission(): array
        {
                return $this->_data;
        }

        /**
         * Get file path.
         * @return string
         */
        public function getFilepath(): string
        {
                return $this->_data['path'];
        }

        /**
         * Get file size.
         * @return int
         */
        public function getFilesize(): int
        {
                return $this->_data['size'];
        }

        /**
         * Get MIME type.
         * @return string
         */
        public function getFiletype(): string
        {
                return $this->_data['type'];
        }

        /**
         * Move uploaded file.
         * 
         * @param string $from The temporary file.
         * @param string $path The target path.
         * @throws RuntimeException
         */
        private function setFilepath(string $from, string $path)
        {
                if (!move_uploaded_file($from, $path)) {
                        throw new RuntimeException("Failed rename uploaded file");
                } else {
                        $this->_data['path'] = $path;
                }
        }

}
