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

use Batchelor\WebService\Types\JobState;

/**
 * The job state.
 * 
 * Simple class representing an state queue item.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class State
{

        /**
         * The host ID.
         * @var string 
         */
        public $hostid;
        /**
         * The result directory.
         * @var string 
         */
        public $result;
        /**
         * The current task.
         * @var string 
         */
        public $task;
        /**
         * The current state.
         * @var JobState 
         */
        public $state;
        /**
         * The queued time (UNIX timestamp).
         * @var int 
         */
        public $queued;
        /**
         * The started time (UNIX timestamp).
         * @var int 
         */
        public $started;
        /**
         * The finished time (UNIX timestamp).
         * @var int 
         */
        public $finished;

        public function __construct(string $hostid, string $task)
        {
                $this->hostid = $hostid;
                $this->result = sprintf("%d%d", time(), rand(1000, 9999));

                $this->task = $task;
                $this->state = JobState::PENDING();

                $this->queued = time();
                $this->started = 0;
                $this->finished = 0;
        }

}
