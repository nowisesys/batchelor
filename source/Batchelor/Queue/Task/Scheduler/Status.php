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

namespace Batchelor\Queue\Task\Scheduler;

/**
 * The scheduler status.
 *
 * @property-read int $count The number of scheduled jobs.
 * @property-read int $index The last queued jobid.
 * @property-read string $timezone The scheduler timezone.
 * @property-read array $queue The queued jobs.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Status
{

        /**
         * Status data.
         * @var array 
         */
        private $_status;

        /**
         * Constructor.
         * @param array $status The status data.
         */
        public function __construct(array $status)
        {
                $this->_status = $status;
        }

        public function __get($name)
        {
                if (isset($this->_status[$name])) {
                        return $this->_status[$name];
                }
        }

}
