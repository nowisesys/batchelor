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

/**
 * Example task counting words in indata.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class WordCounter extends TaskAdapter
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
                $text = $workdir->getFile("indata")->getContent();

                // 
                // Write to result file that is automatic created on write.
                // 
                $file->putContent(str_word_count($text));
        }

}
