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
 * The queued job.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class QueuedJob
{

        /**
         * The job identity.
         * @var JobIdentity 
         */
        public $identity;
        /**
         * The job status.
         * @var JobStatus
         */
        public $status;
        /**
         * The submitted job.
         * @var JobSubmit 
         */
        public $submit;

        /**
         * Constructor.
         * @param JobIdentity $identity The job identity.
         * @param JobStatus $status The job status.
         */
        public function __construct(JobIdentity $identity, JobStatus $status, JobSubmit $submit)
        {
                $this->identity = $identity;
                $this->status = $status;
                $this->submit = $submit;
        }

}
