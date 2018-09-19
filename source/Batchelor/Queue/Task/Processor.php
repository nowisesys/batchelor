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
 * The scheduled task processor.
 * 
 * Should be run as a command line (CLI) task that consumes queued jobs from the 
 * job scheduler. Queries the processor service for a matching task processor to 
 * handle the queued job.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Processor extends Component
{

        /**
         * The time to exit flag.
         * @var bool 
         */
        private $_done = false;

        /**
         * Run queued jobs.
         */
        public function run()
        {
                $scheduler = new Scheduler();

                while (!$this->_done) {
                        if (!$scheduler->hasJobs()) {
                                sleep(5);
                                continue;
                        }

                        if (($runtime = $scheduler->popJob())) {
                                // TODO: Create and execute task.
                        }
                }
        }

}
