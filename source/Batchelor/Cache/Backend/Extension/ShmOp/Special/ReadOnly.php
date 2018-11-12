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

use BadMethodCallException;
use Batchelor\Cache\Backend\Extension\ShmOp\Segment;

/**
 * Read-only memory segment.
 * 
 * This is a specialization of the generic segment class. Use this flag when 
 * you need to open an existing shared memory segment for read-only.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class ReadOnly extends Segment
{

        public function __construct(string $key, int $mode)
        {
                parent::__construct($key, Segment::OPEN_ACCESS, $mode, 0);
        }

        public function delete()
        {
                throw new BadMethodCallException("Can't delete read-only shared memory segment");
        }

}
