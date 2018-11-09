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

use Batchelor\Queue\Task\Adapter;
use Batchelor\Queue\Task\Interaction;
use Batchelor\Storage\Directory;
use Batchelor\System\Service\Processor;
use Batchelor\System\Services;
use Batchelor\WebService\Types\JobData;

// 
// Insert some dummy task.
// 

class DummyTask extends Adapter
{

        public function execute(Directory $workdir, Directory $result, Interaction $interact)
        {
                
        }

        public function validate(JobData $data)
        {
                
        }

}

$task = new DummyTask();

$processor = new Processor();
$processor->setDefault(new DummyTask());

for ($i = 0; $i < 5; ++$i) {
        $processor->setProcessor("task$i", new DummyTask());
}

$services = Services::getInstance();
$services->register('processor', $processor);
