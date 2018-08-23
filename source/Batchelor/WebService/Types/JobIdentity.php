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
 * The job identity class.
 *
 * Contains the data for identifying a queued job relative to the current used 
 * batch job queue. The jobID is the assigned execution order in the batch queue 
 * and the result is physical root directory.
 * 
 * This class should be used as a message passed from peer when performing tasks
 * like stat() an existing job. Client side should treat it as opaque and don't
 * alter its content.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class JobIdentity
{

        /**
         * The job identity.
         * @var string 
         */
        public $jobID;
        /**
         * The root directory.
         * @var string
         */
        public $result;

        /**
         * Constructor.
         * @param string $jobId The job identity.
         * @param string $result The root directory.
         */
        public function __construct($jobId, $result)
        {
                $this->jobID = $jobId;
                $this->result = $result;
        }

}
