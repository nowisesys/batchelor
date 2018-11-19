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

namespace Batchelor\Queue\Task;

use Batchelor\System\Component;

/**
 * The job owner.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Owner
{

        /**
         * The remote address.
         * @var string
         */
        public $addr;
        /**
         * The remote hostname.
         * @var string 
         */
        public $host;
        /**
         * The authenticated user (if any).
         * @var string 
         */
        public $user;
        /**
         * The host ID.
         * @var string 
         */
        public $hostid;

        /**
         * Constructor.
         * @param string $hostid The host ID.
         */
        public function __construct(string $hostid)
        {
                $this->hostid = $hostid;

                $this->addr = filter_input(INPUT_SERVER, "REMOTE_ADDR");
                $this->host = gethostbyaddr($this->addr);
        }

}
