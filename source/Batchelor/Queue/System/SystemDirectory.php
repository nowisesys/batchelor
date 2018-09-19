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

namespace Batchelor\Queue\System;

use Batchelor\Queue\WorkDirectory;
use Batchelor\Storage\Directory;
use Batchelor\System\Component;
use Batchelor\WebService\Types\JobIdentity;

/**
 * The local queue directory.
 * 
 * This class represent the top directory for a system local work queue. All
 * operations are relative to its root directory.
 * 
 * The jobs directory might look like this:
 * 
 * <pre>
 * jobs/                                        <-- jobs  (root)
 * ...
 *  ├── 28b596f64f8d32c867953db56a66f7c3        <-- queue (hostid)
 *  │   ├── 1274776802                          <-- work  (result)
 *  │   ├── 1274944852                          <-- work  (result)
 *  │   └── 1275603815                          <-- work  (result)
 * ...
 *  └── ffcd1b0ceb4c57cd6612b29bb7ee3b89        <-- queue (hostid)
 *      ├── 1371051594                          <-- work  (result)
 *      └── 1371150403                          <-- work  (result)
 * </pre>
 *
 * The jobs directory is the root directory containing all work queues, one per
 * hostid. Each queue contains zero or more work directory. The work directory is
 * where each queued job is run from.
 * 
 * Notice that enqueue a single job might create multiple work directories if 
 * application decides to split indata among multiple sub tasks. Standard is that
 * each submitted job is scheduled in a single work.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class SystemDirectory extends Component implements WorkDirectory
{

        /**
         * The host ID.
         * @var string 
         */
        private $_hostid;

        /**
         * Constructor.
         * @param string $hostid The host ID.
         */
        public function __construct($hostid)
        {
                $this->_hostid = $hostid;
        }

        /**
         * {@inheritdoc}
         */
        public function getContent(JobIdentity $job, string $file, bool $return = true): string
        {
                if ($return) {
                        return $this->getWorkDirectory($job->result)
                                ->getFile($file)
                                ->getContent();
                } else {
                        return $this->getWorkDirectory($job->result)
                                ->getFile($file)
                                ->sendFile();
                }
        }

        /**
         * {@inheritdoc}
         */
        public function getFiles(JobIdentity $job)
        {
                return $this->getWorkDirectory($job->result)
                        ->scan();
        }

        /**
         * {@inheritdoc}
         */
        public function getJobs()
        {
                // TODO: implement (how to handle the jobid for job identity?)
        }

        /**
         * The jobs directory.
         * 
         * This is the root directory containing all queues.
         * @return Directory 
         */
        public function getJobsDirectory(): Directory
        {
                return $this->data->useDirectory("jobs");
        }

        /**
         * The queue directory.
         * 
         * Directory containing all work directories for this hostid.
         * @return Directory 
         */
        public function getQueueDirectory(): Directory
        {
                return $this->data->useDirectory(
                        sprintf("jobs/%s", $this->_hostid)
                );
        }

        /**
         * The work directory.
         * 
         * Directory where an enqueued job is run from. Scheduler an job will 
         * typical place indata (jobdata) inside this directory for the queued 
         * task to process.
         * 
         * @return Directory 
         */
        public function getWorkDirectory(string $result): Directory
        {
                return $this->data->useDirectory(
                        sprintf("jobs/%s/%s", $this->_hostid, $result)
                );
        }

        /**
         * The work result directory.
         * 
         * Directory where an enqueued job should output the result from its
         * computation. When a result download is requested, the content of this 
         * directory will be sent.
         * 
         * @return Directory 
         */
        public function getResultDirectory(string $result): Directory
        {
                return $this->data->useDirectory(
                        sprintf("jobs/%s/%s/result", $this->_hostid, $result)
                );
        }

}
