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

namespace Batchelor\Cache\Backend\Extension\ShmOp;

class Segment implements Handler
{

        /**
         * For access (sets SHM_RDONLY for shmat).
         */
        const OPEN_ACCESS = "a";
        /**
         * For create (sets IPC_CREATE).
         */
        const OPEN_CREATE = "c";
        /**
         * For read & write access.
         */
        const OPEN_WRITE = "w";
        /**
         * Create a new memory segment (sets IPC_CREATE | IPC_EXCL).
         */
        const OPEN_PRIVATE = "n";

        /**
         * The shared memory key.
         * @var int 
         */
        private $_id;
        /**
         * The shared memory handle.
         * @var resource 
         */
        private $_handle;
        /**
         * The open flags.
         * @var string 
         */
        private $_flag;
        /**
         * The open mode.
         * @var int 
         */
        private $_mode;
        /**
         * The requested number of bytes.
         * @var int
         */
        private $_size;
        /**
         * The number generator.
         * @var Generator 
         */
        private static $_generator;

        /**
         * Constructor.
         * 
         * @param string $key The segment name,
         * @param string $flag The open flags.
         * @param int $mode The open mode (see OPEN_XXX constants).
         * @param int $size The requested number of bytes.
         */
        public function __construct(string $key, string $flag, int $mode, int $size)
        {
                $this->create($key, $flag, $mode, $size);
        }

        /**
         * Destructor.
         */
        public function __destruct()
        {
                $this->close();
        }

        /**
         * {@inheritdoc}
         */
        public function isOpen(): bool
        {
                return is_resource($this->_handle);
        }

        /**
         * {@inheritdoc}
         */
        public function isReadOnly(): bool
        {
                return $this->_flag == self::OPEN_ACCESS;
        }

        /**
         * {@inheritdoc}
         */
        public function getSize(): int
        {
                return shmop_size($this->_handle);
        }

        /**
         * Get shared memory handle.
         * @return resource
         */
        public function getHandle(): resource
        {
                return $this->_handle;
        }

        /**
         * Get shared memory key.
         * @return int
         */
        public function getID(): int
        {
                return $this->_id;
        }

        /**
         * Get open mode.
         * @return int
         */
        public function getMode(): int
        {
                return $this->_mode;
        }

        /**
         * Set open mode.
         * 
         * The mode will be applied on next call to open(), reopen() or resize().
         * @param int $mode
         */
        public function setMode(int $mode)
        {
                $this->_mode = $mode;
        }

        /**
         * Get open flags.
         * @return string
         */
        public function getFlag(): string
        {
                return $this->_flag;
        }

        /**
         * Check if shared memory segment exists.
         * 
         * This could be useful for checking if a segment already exists before
         * opening it as exclusive.
         * 
         * @param string $key The segment name,
         * @return bool
         */
        public static function exist(string $key): bool
        {
                return @(new Segment($key, self::OPEN_ACCESS, 0666, 0))->isOpen();
        }

        /**
         * Compute shared memory key.
         * 
         * @param string $key The segment name,
         * @return int
         */
        public static function id(string $key): int
        {
                return ftok(__FILE__, "1") + hexdec(substr(md5($key), 0, 15));
        }

        /**
         * {@inheritdoc}
         */
        public function open()
        {
                $this->_handle = shmop_open($this->_id, $this->_flag, $this->_mode, $this->_size);
        }

        /**
         * {@inheritdoc}
         */
        public function close()
        {
                if (is_resource($this->_handle)) {
                        shmop_close($this->_handle);
                        $this->_handle = null;
                }
        }

        /**
         * {@inheritdoc}
         */
        public function delete(): bool
        {
                $result = shmop_delete($this->_handle);
                $this->close();
                return $result;
        }

        /**
         * {@inheritdoc}
         */
        public function read(int $start = 0, int $count = 0): string
        {
                return shmop_read($this->_handle, $start, $count);
        }

        /**
         * {@inheritdoc}
         */
        public function write(string $data, int $offset = 0): int
        {
                return shmop_write($this->_handle, $data, $offset);
        }

        /**
         * {@inheritdoc}
         */
        public function reopen()
        {
                $this->close();
                $this->open();
        }

        /**
         * {@inheritdoc}
         */
        public function resize(int $size, bool $copy = false)
        {
                if ($copy) {
                        $data = $this->read();
                        $this->resize($size);
                        $this->write($data);
                } else {
                        $this->_size = $size;
                        $this->close();
                        $this->open();
                }
        }

        /**
         * Set members and open shared memory segment.
         * 
         * @param string $key The segment name.
         * @param string $flag The open flags.
         * @param int $mode The open mode.
         * @param int $size The requested number of bytes.
         */
        private function create(string $key, string $flag, int $mode, int $size)
        {
                $this->_id = self::id($key);

                $this->_flag = $flag;
                $this->_mode = $mode;
                $this->_size = $size;

                $this->open();
        }

}
