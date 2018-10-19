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

namespace Batchelor\Render;

use Batchelor\System\Component;
use Batchelor\WebService\Types\QueueFilterResult;
use Batchelor\WebService\Types\QueueSortResult;

/**
 * The queue render component.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Queue extends Component
{

        /**
         * The render template.
         * @var string 
         */
        private $_template;

        /**
         * Constructor.
         * @param string $template The render template.
         */
        public function __construct(string $template)
        {
                $this->_template = $template;
        }

        /**
         * Render queue.
         * 
         * @param string $sort The queue sort options.
         * @param string $filter The queue filter options.
         */
        public function render(string $sort, string $filter)
        {
                $queued = $this->getJobs($sort, $filter);
                include($this->_template);
        }

        /**
         * Get queued jobs.
         * 
         * @param string $sort The queue sort options.
         * @param string $filter The queue filter options.
         * @return array 
         */
        private function getJobs(string $sort, string $filter): array
        {
                $hostid = $this->hostid->getValue();
                $queued = $this->queue->listJobs($hostid, new QueueSortResult($sort), new QueueFilterResult($filter));

                return $queued ?? [];
        }

}
