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

use Batchelor\Queue\Task\Scheduler;

/**
 * The scheduler summary.
 * 
 * @property-read int $index Last used index.
 * @property-read int $count Total number of jobs.
 * @property-read int $pending Number of pending jobs.
 * @property-read int $running Number of running jobs.
 * @property-read string $timezone The server timezone.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Summary
{

        /**
         * The property bag.
         * @var array 
         */
        private $_data = [];

        /**
         * Constructor.
         * @param Scheduler $scheduler The schedule object.
         */
        public function __construct(Scheduler $scheduler)
        {
                $this->setTimezone();
                $this->setPending($scheduler);
                $this->setRunning($scheduler);
        }

        public function __get($name)
        {
                if (isset($this->_data[$name])) {
                        return $this->_data[$name];
                }
        }

        private function setTimezone()
        {
                $this->_data['timezone'] = ini_get("date.timezone");
        }

        private function setPending(Scheduler $scheduler)
        {
                $this->_data['pending'] = $scheduler->getQueue("pending")->getSize();
        }

        private function setRunning(Scheduler $scheduler)
        {
                $this->_data['running'] = $scheduler->getQueue("running")->getSize();
        }

}
