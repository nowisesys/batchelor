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

namespace Application;

use Batchelor\Queue\Task\Adapter;
use Batchelor\Queue\Task\Interaction;
use Batchelor\Storage\Directory;
use Batchelor\WebService\Types\JobData;
use Batchelor\WebService\Types\JobState;

/**
 * Example task reversing text in indata.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class ReverseTextTask extends Adapter
{

        public function execute(Directory $workdir, Directory $result, Interaction $interact)
        {
                // 
                // Send an greeting. This message should end up in the task
                // specific log file.
                //                 
                $interact->getLogger()->info("Hello world from reverse text");

                // 
                // Create output file in results directory. Pick up the input
                // prepared input data:
                // 
                $file = $result->getFile("output-reverse.txt");
                $text = $workdir->getFile("indata")->getContent();

                // 
                // Write task result:
                // 
                $file->putContent(strrev($text));

                // 
                // Run a sub task. Calling runTask() will run the wordcount task 
                // processor using file as input data. Opposite to calling newTask(),
                // this will not schedule a new job, instead the wordcount task is
                // run in the same thread as current task.
                // 
                // We're using pipe to signal that file should be symlinked instead 
                // of moved if setTarget() is called.
                // 
                $data = new JobData($file->getPathname(), 'pipe', 'wordcount');
                $interact->runTask($data);

                // 
                // This is our main task, so we should set job status when this 
                // task has finished. Notice that calling die(), exit or throwing
                // exceptions are supervised.
                // 
                // Throwing an exception in this or a child task should be trapped
                // and set job status to error state.
                // 
                $interact->setStatus(JobState::SUCCESS());
        }

}
