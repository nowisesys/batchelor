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
 * Job state enum.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class JobState extends QueueFilterResult
{

        /**
         * Restart an finished job.
         */
        const RESTART = 'restart';

        /**
         * Constructor.
         */
        public function __construct(string $state = parent::PENDING)
        {
                parent::__construct($state);
        }

        /**
         * Get color coding for status.
         * @return string
         */
        public function getColor(): string
        {
                switch ($this->value) {
                        case self::CRASHED:
                        case self::ERROR:
                                return "red";
                        case self::FINISHED:
                                return "green";
                        case self::PENDING:
                                return "light-grey";
                        case self::RUNNING:
                                return "green";
                }
        }

        /**
         * Get label for remove job.
         * @return string
         */
        public function getRemove(): string
        {
                switch ($this->value) {
                        case self::PENDING:
                        case self::CONTINUED:
                        case self::RUNNING:
                                return _("Cancel");
                        default:
                                return _("Delete");
                }
        }

}
