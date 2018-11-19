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

namespace Application\Task;

use Batchelor\Queue\Task\Adapter as TaskAdapter;
use Batchelor\Queue\Task\Interaction;
use Batchelor\Storage\Directory;
use Batchelor\WebService\Types\JobData;
use Batchelor\WebService\Types\JobState;

/**
 * The combined task.
 * 
 * This demonstrate running a combined set of sub task. Same principle can be
 * applied for running sub tasks from a sub task. Keep in mind that each task
 * is run in their own work directory.
 * 
 * Calling runTask() will run a child task of this task. This is different from
 * newTask() that will push the task onto the scheduler. The pipe argument causes
 * the data to be linked as input data for a sub task.
 * 
 * <code>
 *      tasks (scheduler)
 *        +-- combined (this task)
 *              +-- reverse (sub task of this task)
 *              +-- counter (sub task of this task)
 * </code>
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class CombineTask extends TaskAdapter
{

        public function execute(Directory $workdir, Directory $result, Interaction $interact)
        {
                $logger = $interact->getLogger();

                $owner = $interact->getOwner();
                $logger->debug("Running job submitted from %s (%s) [user: %s]", [$owner->host, $owner->addr, $owner->user]);

                $logger->debug("Running reverse task as child task");
                $indata = $workdir->getFile("indata")->getPathname();
                $interact->runTask(new JobData($indata, "pipe", "reverse"));

                $logger->debug("Running counter task as child task");
                $indata = $result->getFile("output-reverse.txt")->getPathname();
                $interact->runTask(new JobData($indata, "pipe", "counter"));

                $interact->setStatus(JobState::SUCCESS());
        }

}
