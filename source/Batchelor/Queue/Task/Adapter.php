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

use Batchelor\Queue\Task;
use Batchelor\Storage\Directory;
use Batchelor\System\Component;
use Batchelor\WebService\Types\JobData;

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
         * The job data.
         * @var JobData 
         */
        protected $_data;

        /**
         * {@inheritdoc}
         */
        public function finished()
        {
                // Ignore
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
        public function prepare(Directory $workdir, JobData $data)
        {
                $this->_data = $data;
        }

}
