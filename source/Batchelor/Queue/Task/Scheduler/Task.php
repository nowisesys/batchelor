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

use Batchelor\WebService\Types\JobData;
use Batchelor\WebService\Types\JobIdentity;

/**
 * Simple data type for serialization.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Task
{

        /**
         * The job data.
         * @var JobData 
         */
        public $data;
        /**
         * The job identity.
         * @var JobIdentity 
         */
        public $identity;

        /**
         * Constructor.
         * 
         * @param JobIdentity $identity The job identity.
         * @param JobData $data The job data.
         */
        public function __construct(JobIdentity $identity, JobData $data)
        {
                $this->identity = $identity;
                $this->data = $data;
        }

}
