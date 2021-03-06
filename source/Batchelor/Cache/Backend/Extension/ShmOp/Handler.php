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

/**
 * The shared memory handler.
 * @author Anders Lövgren (Nowise Systems)
 */
interface Handler
{

        /**
         * Get shared memory segment size.
         * 
         * @return int The size in bytes.
         */
        function getSize(): int;

        /**
         * Check if segment is open.
         * 
         * @return bool
         */
        function isOpen(): bool;

        /**
         * Check if segment is opened read-only.
         * 
         * @return bool 
         */
        function isReadOnly(): bool;

        /**
         * Open the memory segment.
         */
        function open();

        /**
         * Close the memory segment.
         */
        function close();

        /**
         * Delete the memory segment.
         * 
         * @return bool True if successful.
         */
        function delete(): bool;

        /**
         * Read data from segment.
         * 
         * @param int $start The start offset.
         * @param int $count The number of bytes to read.
         * @return string 
         */
        function read(int $start = 0, int $count = 0): string;

        /**
         * Write data to segment.
         * 
         * @param string $data The input data.
         * @param int $offset Optional offset from memory segment start.
         * @return int The number of bytes written.
         */
        function write(string $data, int $offset = 0): int;

        /**
         * Reopen memory segment.
         */
        function reopen();

        /**
         * Resize the memory segment.
         * 
         * @param int $size The new size.
         * @param bool $copy Copy data to new segment.
         */
        function resize(int $size, bool $copy = false);
}
