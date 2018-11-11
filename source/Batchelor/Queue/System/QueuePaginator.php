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

use Batchelor\Queue\Task\Scheduler\StateQueue;
use Batchelor\WebService\Types\JobState;
use Batchelor\WebService\Types\QueueFilterResult;
use Batchelor\WebService\Types\QueueSortResult;
use RuntimeException;

/**
 * The queue paginator.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class QueuePaginator
{

        /**
         * The state queue.
         * @var StateQueue 
         */
        private $_queue;
        /**
         * The queue filter to apply.
         * @var QueueFilterResult 
         */
        private $_filter;
        /**
         * The queue sort to apply.
         * @var QueueSortResult 
         */
        private $_sort;

        /**
         * Constructor.
         * @param StateQueue $queue The state queue.
         */
        public function __construct(StateQueue $queue)
        {
                $this->_queue = $queue;

                $this->_filter = QueueFilterResult::NONE();
                $this->_sort = QueueSortResult::NONE();
        }

        /**
         * Set filter mode.
         * @param QueueFilterResult $filter The queue filter.
         */
        public function setFilter(QueueFilterResult $filter)
        {
                $this->_filter = $filter;
        }

        /**
         * Set sort mode.
         * @param QueueSortResult $sort
         */
        public function setSorting(QueueSortResult $sort)
        {
                $this->_sort = $sort;
        }

        /**
         * Get queue slice.
         * 
         * @param int $offset The offset from start.
         * @param int $length The number of items,
         * @return array 
         */
        public function getSlice(int $offset = 0, int $length = 0): array
        {
                $queued = $this->getFiltered($this->_filter);
                $queued = $this->getSorted($this->_sort, $queued);

                if ($offset == 0 && $length == 0) {
                        return $queued;
                } else {
                        return array_slice($queued, $offset, $length);
                }
        }

        /**
         * Check if filter mathes job state.
         * 
         * For pending, running and finished filter we are making a fuzzy match 
         * on job phase. Recent jobs are checked for good status. For all other 
         * filters the match is exact.
         * 
         * @param QueueFilterResult $filter The queue filter.
         * @param JobState $state The job state.
         * @return bool True if state is matched.
         */
        private function isMatched(QueueFilterResult $filter, JobState $state): bool
        {
                switch ($filter->getValue()) {
                        case QueueFilterResult::NONE:           // Include all
                                return true;
                        case QueueFilterResult::PENDING:
                                return $state->isPending();
                        case QueueFilterResult::RUNNING:
                                return $state->isStarted();
                        case QueueFilterResult::RECENT:
                                return $state->isGood();
                        case QueueFilterResult::COMPLETED:
                                return $state->isCompleted();
                        default:
                                return $state->getValue() == $filter->getValue();
                }
        }

        /**
         * Get filtered queue.
         * 
         * @param QueueFilterResult $filter The queue filter.
         * @return array
         */
        private function getFiltered(QueueFilterResult $filter): array
        {
                $queued = [];

                foreach ($this->_queue as $jobid => $state) {
                        if ($this->isMatched($filter, $state->status->state)) {
                                $queued[] = $state->getQueuedJob($jobid);
                        }
                }

                return $queued;
        }

        /**
         * Get sorted queue.
         * 
         * @param QueueSortResult $sort The queue sort.
         * @param array $queued The input queue.
         * @return array
         * @throws RuntimeException
         */
        private function getSorted(QueueSortResult $sort, array &$queued): array
        {
                switch ($sort->getValue()) {
                        case QueueSortResult::JOBID:
                                if (!usort($queued, static function($a, $b) {
                                            return strcmp($a->identity->jobid, $b->identity->jobid);
                                    })) {
                                        throw new RuntimeException("Failed sort state queue result");
                                } else {
                                        return $queued;
                                }
                        case QueueSortResult::NAME:
                                if (!usort($queued, static function($a, $b) {
                                            return strcmp($a->submit->name, $b->submit->name);
                                    })) {
                                        throw new RuntimeException("Failed sort state queue result");
                                } else {
                                        return $queued;
                                }
                        case QueueSortResult::STARTED:
                                if (!usort($queued, static function($a, $b) {
                                            return strcmp($a->identity->jobid, $b->identity->jobid);
                                    })) {
                                        throw new RuntimeException("Failed sort state queue result");
                                } else {
                                        return $queued;
                                }
                        case QueueSortResult::STATE:
                                if (!usort($queued, static function($a, $b) {
                                            return strcmp($a->status->state->getValue(), $b->status->state->getValue());
                                    })) {
                                        throw new RuntimeException("Failed sort state queue result");
                                } else {
                                        return $queued;
                                }
                        case QueueSortResult::TASK:
                                if (!usort($queued, static function($a, $b) {
                                            return strcmp($a->submit->task, $b->submit->task);
                                    })) {
                                        throw new RuntimeException("Failed sort state queue result");
                                } else {
                                        return $queued;
                                }
                        case QueueSortResult::PUBLISHED:
                                // TODO: Do we need to support published?
                                throw new RuntimeException("Not yet implemented");
                        default:
                                throw new RuntimeException(sprintf("Unknown sort mode %s", $sort->getValue()));
                }
        }

}
