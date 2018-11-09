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

namespace Batchelor\Queue\Task\Scheduler\Action;

use Batchelor\Queue\Task\Scheduler;
use Batchelor\System\Component;
use Batchelor\WebService\Types\JobData;
use RuntimeException;

/**
 * Add job to scheduler.
 * 
 * Adding an job is not the same as pushing an job onto scheduler. When adding, 
 * we attaching a sub job to an already job.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Add extends Component
{

        /**
         * The scheduler object.
         * @var Scheduler
         */
        private $_scheduler;

        /**
         * Constructor.
         * @param Scheduler $scheduler The scheduler object.
         */
        public function __construct(Scheduler $scheduler)
        {
                $this->_scheduler = $scheduler;
        }

        public function execute(string $job, JobData $data)
        {
                if (!$this->processor->hasProcesor($data->task)) {
                        throw new RuntimeException("The task processor $data->task is missing");
                }

                // TODO: Add sub task to job.
                throw new RuntimeException("Adding sub task is not yet implemented");
        }

}
