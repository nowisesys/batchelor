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
 * Sort mode enum.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class QueueSortResult extends EnumType
{

        /**
         * Don't sort queue.
         */
        const NONE = 'none';
        /**
         * Sort on started datetime.
         */
        const STARTED = 'started';
        /**
         * Sort on job ID.
         */
        const JOBID = 'jobid';
        /**
         * Sort on job state.
         */
        const STATE = 'state';
        /**
         * Sort on job name.
         */
        const NAME = 'name';
        /**
         * Sort on published mode.
         */
        const PUBLISHED = 'published';
        /**
         * Sort on current task.
         */
        const TASK = 'task';

        /**
         * Constructor.
         * @param string $value The sort mode.
         */
        public function __construct(string $value = self::NONE)
        {
                parent::__construct($value, __CLASS__);
        }

        /**
         * Create queue sort result object.
         * 
         * @param array $data The enum data.
         * @return QueueSortResult
         */
        public static function create(array $data): self
        {
                if (empty($data) || !isset($data['sort'])) {
                        $data['sort'] = self::NONE;
                }

                return new self($data['sort']);
        }

}
