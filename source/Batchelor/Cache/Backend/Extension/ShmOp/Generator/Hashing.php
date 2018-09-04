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

namespace Batchelor\Cache\Backend\Extension\ShmOp\Generator;

use Batchelor\Cache\Backend\Extension\ShmOp\Generator;

/**
 * Hashing number generator.
 *
 * Uses a combination of ftok() and hexdec() for generating a possibly
 * unique number. The input for hexdec is MD5 sum of key, but truncated
 * to prevent integer overflow.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Hashing implements Generator
{

        /**
         * Get next number.
         * 
         * @param string $key The segment name.
         * @return int The System V IPC resource key.
         */
        public function next(string $key): int
        {
                return ftok(__FILE__, "1") + hexdec(substr(md5($key), 0, 15));
        }

}
