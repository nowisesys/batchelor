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

use Batchelor\Queue\System\SystemDirectory;
use Batchelor\Storage\Directory;
use Batchelor\WebService\Types\JobData;
use Batchelor\WebService\Types\QueuedJob;

/**
 * The runtime data.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Runtime
{

        /**
         * The queued job.
         * @var QueuedJob 
         */
        public $meta;
        /**
         * The job data.
         * @var JobData 
         */
        public $data;
        /**
         * The runtime hostid.
         * @var string
         */
        public $hostid;

        /**
         * Constructor.
         * 
         * @param QueuedJob $meta The queued job.
         * @param Jobdata $data The job data.
         * @param string $hostid The hostid.
         */
        public function __construct(QueuedJob $meta, Jobdata $data, string $hostid = null)
        {
                $this->meta = $meta;
                $this->data = $data;
                $this->hostid = $hostid;
        }

        public function getWorkDirectory(): Directory
        {
                return (new SystemDirectory($this->hostid))
                        ->getWorkDirectory(
                            $this->meta->identity->result
                );
        }

        public function getResultDirectory(): Directory
        {
                return (new SystemDirectory($this->hostid))
                        ->getResultDirectory(
                            $this->meta->identity->result
                );
        }

}
