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
use Batchelor\WebService\Types\JobState;

/**
 * Example task reversing text in indata.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class ReverseText extends TaskAdapter
{

        public function execute(Directory $workdir, Directory $result, Interaction $interact)
        {
                $logger = $interact->getLogger();
                
                // 
                // Send an greeting. This message should end up in the task
                // specific log file.
                //                 
                $logger->info("Hello world from reverse text");

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
                // Will set success state if this is out main task (this task is 
                // not running as a sub task).
                // 
                $interact->setStatus(JobState::SUCCESS());
        }

}
