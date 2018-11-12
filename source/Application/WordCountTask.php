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
use InvalidArgumentException;

/**
 * Example task counting words in indata.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class WordCountTask extends Adapter
{

        public function execute(Directory $workdir, Directory $result, Interaction $interact)
        {
                // 
                // Send an greeting. This message should end up in the task
                // specific log file.
                // 
                $interact->getLogger()->info("Hello world from word counter");

                // 
                // Create output file in results directory. Pick up the input
                // prepared input data:
                // 
                $file = $result->getFile("output-wordcount.txt");
                $text = $workdir->getFile("input.txt")->getContent();

                // 
                // Write to result file that is automatic created on write.
                // 
                $file->putContent(str_word_count($text));
        }

        public function validate(JobData $data)
        {
                if (filesize($data->data) == 0) {
                        throw new InvalidArgumentException("Input data is empty");
                }
        }

        public function prepare(Directory $workdir, JobData $data)
        {
                // 
                // The utility method setTarget() will relocate job data into
                // work directory (i.e. move uploaded file or download URL). Its
                // usage is optional.
                // 
                // Calling getFile() creates an file object relative to the
                // working directory that is actually not created until it's
                // written. Here we use it just to make a file path.
                // 
                $file = $workdir->getFile("input.txt");
                $data->setTarget($file->getPathname(), true);
        }

}
