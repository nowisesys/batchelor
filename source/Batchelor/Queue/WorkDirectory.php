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

namespace Batchelor\Queue;

use Batchelor\WebService\Types\JobIdentity;

/**
 * Interface for work queue directories.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
interface WorkDirectory
{

        /**
         * Get job identities.
         * @return JobIdentity[]
         */
        function getJobs();

        /**
         * Get job files.
         * @param JobIdentity $job The job identity.
         * @return string[]
         */
        function getFiles(JobIdentity $job);

        /**
         * Get file content.
         * 
         * Read single file from the job identified by the job argument. Returns 
         * file content as string or send content to stdout.
         * 
         * @param JobIdentity $job The job identity.
         * @param string $file The file to read.
         * @param bool $return Return file content.
         * @return string The file content.
         */
        function getContent(JobIdentity $job, string $file, bool $return = true);
}
