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

namespace Batchelor\WebService\Types;

/**
 * The job status class.
 * 
 * Represent the runtime status of an queued job. Contains the current execution 
 * state (i.e. pending, finished or failed) and the datetime of enquing. The stamp
 * is the UNIX timestamp for enqeuing. Use the timezone member for localizining 
 * time values.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class JobStatus
{

        /**
         * The enqueue date.
         * @var string 
         */
        public $date;
        /**
         * The enqueue time.
         * @var string 
         */
        public $time;
        /**
         * The server timezone.
         * @var string 
         */
        public $timezone;
        /**
         * The enqueue UNIX timestamp.
         * @var int 
         */
        public $stamp;
        /**
         * The job state.
         * @var string 
         */
        public $state;

        /**
         * Constructor.
         * 
         * @param string $date The enqueue date.
         * @param string $time The enqueue time.
         * @param int $stamp The enqueue UNIX timestamp.
         * @param JobState $state The job state.
         */
        public function __construct($date, $time, $stamp, $state)
        {
                $this->date = $date;
                $this->time = $time;
                $this->stamp = $stamp;
                $this->state = $state;
                $this->timezone = ini_get("date.timezone");
        }

}
