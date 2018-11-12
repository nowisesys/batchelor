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
 * Exclusive memory segment.
 * 
 * This is a specialization of the generic segment class. Use this class when 
 * you want to create a new shared memory segment but if one already exists with 
 * the same flag, fail. This is useful for security purposes, using this you can 
 * prevent race condition exploits.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Exclusive extends Segment
{

        public function __construct(string $key, int $mode, int $size)
        {
                parent::__construct($key, Segment::OPEN_PRIVATE, $mode, $size);
        }

}
