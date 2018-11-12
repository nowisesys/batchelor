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

namespace Batchelor\Cache\Backend\Extension\ShmOp\Special;

use Batchelor\Cache\Backend\Extension\ShmOp\Segment;

/**
 * Writable memory segment.
 * 
 * This is a specialization of the generic segment class. Use this class when 
 * you need to read and write to a shared memory segment, use this flag in most 
 * cases.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Writable extends Segment
{

        public function __construct(string $key, int $mode, int $size)
        {
                parent::__construct($key, Segment::OPEN_WRITE, $mode, $size);
        }

}
