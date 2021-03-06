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
 * Filter mode enum.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class QueueFilterResult extends EnumType
{

        /**
         * Don't filter anything.
         */
        const NONE = 'none';
        /**
         * Job is queued, but not yet started.
         */
        const PENDING = 'pending';
        /**
         * Job is running, but not yet finished.
         */
        const RUNNING = 'running';
        /**
         * Job has finished (includes all states).
         */
        const FINISHED = 'finished';
        /**
         * Finished successful.
         */
        const SUCCESS = 'success';
        /**
         * Finished with warnings.
         */
        const WARNING = 'warning';
        /**
         * Finished with errors.
         */
        const ERROR = 'error';
        /*
         * The job has crashed (i.e. segmentation fault).
         */
        const CRASHED = 'crashed';
        /**
         * Finished with success or warnings.
         */
        const COMPLETED = 'completed';
        /**
         * Include recently finished or ongoing jobs.
         */
        const RECENT = 'recent';
        /**
         * Job is suspended.
         */
        const SUSPEND = 'suspend';
        /**
         * Job is resumed.
         */
        const RESUMED = 'resumed';
        /**
         * Waiting for sub job to complete.
         */
        const CONTINUED = 'continued';
        /**
         * Alias for NONE.
         */
        const ALL = 'none';
        /**
         * Alias for PENDING.
         */
        const WAITING = 'pending';

        /**
         * Constructor.
         * @param string $value The filter mode.
         */
        public function __construct(string $value = self::NONE)
        {
                parent::__construct($value, __CLASS__);
        }

        /**
         * Create queue filter result object.
         * 
         * @param array $data The enum data.
         * @return QueueFilterResult
         */
        public static function create(array $data): self
        {
                if (empty($data) || !isset($data['filter'])) {
                        $data['filter'] = self::NONE;
                }

                return new self($data['filter']);
        }

}
