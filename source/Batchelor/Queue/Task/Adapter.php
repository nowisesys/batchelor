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

use Batchelor\Logging\Logger;
use Batchelor\Queue\Task;
use Batchelor\Storage\Directory;
use Batchelor\System\Component;
use Batchelor\WebService\Types\JobData;
use InvalidArgumentException;
use RuntimeException;

/**
 * The task adapter class.
 * 
 * Use this class as base for concrete task classes to refrain from having to
 * implement all methods in the task interface.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
abstract class Adapter extends Component implements Task
{

        /**
         * {@inheritdoc}
         */
        public function run(Runtime $runtime, Logger $info)
        {
                $workdir = $runtime
                    ->getWorkDirectory()
                    ->create($runtime->data->task);

                $results = $runtime
                    ->getResultDirectory()
                    ->create();

                $info->info("Preparing runtime data");
                $this->prepare($workdir, $runtime->data);

                $info->info("Validating runtime data");
                $this->validate($runtime->data, $runtime->owner);

                $info->info("Initialize task %s for execute", [$runtime->data->task]);
                $this->initialize();

                $info->info("Execute task for job %s", [$runtime->data->name]);
                $this->execute($workdir, $results, $runtime->getCallback());

                $info->info("Finished running task");
                $this->finished();

                $workdir
                    ->getFile("indata.ser")
                    ->putContent(serialize($runtime->data));
        }

        /**
         * {@inheritdoc}
         */
        public function prepare(Directory $workdir, JobData $data)
        {
                $data->setTarget($workdir->getFile("indata")->getPathname(), true);
        }

        /**
         * {@inheritdoc}
         */
        public function validate(JobData $data, Owner $owner)
        {
                if (!$data->getFile()->isFile()) {
                        throw new InvalidArgumentException("Input file is missing");
                }
                if (filesize($data->data) == 0) {
                        throw new InvalidArgumentException("Input data is empty");
                }
        }

        /**
         * {@inheritdoc}
         */
        public function initialize()
        {
                // Ignore
        }

        /**
         * {@inheritdoc}
         */
        public function execute(Directory $workdir, Directory $result, Interaction $interact)
        {
                throw new RuntimeException("The method execute() need to be implemented");
        }

        /**
         * {@inheritdoc}
         */
        public function finished()
        {
                // Ignore
        }

}
