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

use Batchelor\Queue\Task\Callback;
use Batchelor\Storage\Directory;
use Batchelor\WebService\Types\JobData;

/**
 * The task interface.
 * 
 * Concrete classes should implement this interface and register themself with the
 * job queue processor service. The class should implement the methods to provide 
 * the business logic that defines your application.
 * 
 * The methods will be called in this order: initialize(), prepare(), execute() and
 * last finished(). The task adapter class can be used that provides dummy methods
 * and some boiler plate code.
 * 
 * @author Anders Lövgren (Nowise Systems)
 * @see Task\Adapter
 */
interface Task
{

        /**
         * The task initialize method.
         */
        function initialize();

        /**
         * Prepare for execution.
         * 
         * @param Directory $workdir The work directory.
         * @param JobData $data The job data to process.
         */
        function prepare(Directory $workdir, JobData $data);

        /**
         * Perform business logic.
         * 
         * @param Directory $workdir The work directory.
         * @param Directory $result The result directory.
         * @param Callback $response The response object with logger.
         */
        function execute(Directory $workdir, Directory $result, Callback $response);

        /**
         * Called when task has finished.
         */
        function finished();
}
