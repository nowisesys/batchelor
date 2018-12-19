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

use Batchelor\Logging\Logger;
use Batchelor\Queue\Task\Adapter;
use Batchelor\Queue\Task\Interaction;
use Batchelor\Queue\Task\Owner;
use Batchelor\Queue\Task\Runtime;
use Batchelor\Storage\Directory;
use Batchelor\WebService\Types\JobData;

/**
 * The task interface.
 * 
 * Concrete classes should implement this interface and register themself with the
 * job queue processor service. The class should implement the methods to provide 
 * the business logic that defines your application.
 * 
 * The methods will be called in this order:
 * 
 * <ol>
 * <li>prepare()    - Prepare data for execution.</li>
 * <li>validate()   - Validate input data for this task.</li>
 * <li>initialize() - Initilize task before execute.</li>
 * <li>execute()    - Called to process input data.</li>
 * <li>finished()   - Cleanup after execution.</li>
 * </ol>
 * 
 * The task adapter is an abstract class that provides dummy implementations for
 * some of the interface methods. The validate() and prepare() method is called
 * to prepare indata, while the initialize(), execute() and finished() methods are 
 * called by the schedule processor to process data.
 * 
 * Throw exceptions or call set error status thru the response callback to signal
 * error condition. New jobs can also be added to scheduler which makes it possible
 * to build chained (workflow) or divided tasks,
 * 
 * The initial task might i.e. chose to split input data into smaller blocks that 
 * is each processed as a separate task.
 * 
 * @author Anders Lövgren (Nowise Systems)
 * @see Adapter
 */
interface Task
{

        /**
         * Run this task.
         * 
         * The main method called by task runner. You should only override this 
         * method in special cases where you need finer control of how the task
         * is executed. 
         * 
         * Calling this method should run the other methods in sequence. The 
         * task adapter class implement this method. 
         * 
         * @param Runtime $runtime The task runtime.
         * @param Logger $logger The message logger.
         * @see Adapter
         */
        function run(Runtime $runtime, Logger $logger);

        /**
         * Prepare for execution.
         * 
         * @param Directory $workdir The work directory.
         * @param JobData $data The job data to process.
         */
        function prepare(Directory $workdir, JobData $data);

        /**
         * Check input data.
         * 
         * @param JobData $data The job data to process.
         * @param Owner $owner The owner of submitted data.
         */
        function validate(JobData $data, Owner $owner);

        /**
         * The task initialize method.
         */
        function initialize();

        /**
         * Perform business logic.
         * 
         * @param Directory $workdir The work directory.
         * @param Directory $result The result directory.
         * @param Interaction $interact The response object with logger.
         */
        function execute(Directory $workdir, Directory $result, Interaction $interact);

        /**
         * Called when task has finished.
         */
        function finished();
}
