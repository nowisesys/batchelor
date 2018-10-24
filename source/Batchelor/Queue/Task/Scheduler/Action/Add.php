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

use Batchelor\Queue\Task\Runtime;
use Batchelor\Queue\Task\Scheduler;
use Batchelor\Queue\Task\Scheduler\State;
use Batchelor\WebService\Types\JobData;

/**
 * Add job to scheduler.
 * 
 * Adding an job is not the same as pushing an job onto scheduler. When adding, 
 * we attaching a sub job to an already job.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Add
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
                $scheduler = $this->_scheduler;

                $runtime = $this->setRuntime($job, $data);
                $state = new State($runtime->hostid, $data->task);

                $queue = $scheduler->getQueue("running");
                $queue->removeState($job);

                $queue = $scheduler->getQueue($runtime->hostid);
                $queue->addState($job, $state);
        }

        private function setRuntime(string $job, JobData $data): Runtime
        {
                $scheduler = $this->_scheduler;

                $runtime = $scheduler->getRuntime($job);
                $runtime->data = $data;
                $runtime->pid = 0;
                $scheduler->setRuntime($job, $runtime);

                return $runtime;
        }

}
