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

namespace Batchelor\WebService\Types;

/**
 * The job data (indata) class.
 * 
 * Represent data used as input for an scheduled job. The job data can be plain 
 * data, an file already on server or an download URL. 
 * 
 * The data is procesed by the task manager. It's the responsibility of the task 
 * to download from an URL and authenticate if required for access. Logon context
 * used when enqueue the job is not accessable in tasks.
 * 
 * The task is the processor that will perform work on the input data. The name
 * must match one of the registered task processors. Most setups have only a single
 * task processor in which case default is a good choice.
 * 
 * The job can be named using the optional name property. When query for jobs in
 * scheduler/queue the same name can be user for sort/filtering.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class JobData
{

        /**
         * The job data (plain data, file path or an URL).
         * @var string 
         */
        public $data;
        /**
         * The data type (either "data", "file" or "url").
         * @var string 
         */
        public $type;
        /**
         * The task processor.
         * @var string 
         */
        public $task;
        /**
         * Optional name for job.
         * @var string 
         */
        public $name;

        /**
         * Constructor.
         * @param string $data The job data (plain data or an URL).
         * @param string $type The data type (either "data" or "url").
         * @param string $task The task processor (i.e. "default").
         */
        public function __construct(string $data, string $type, string $task = 'default', string $name = null)
        {
                $this->data = $data;
                $this->type = $type;
                $this->task = $task;
                $this->name = $name;
        }

}
