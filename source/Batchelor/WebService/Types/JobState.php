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

use RuntimeException;

/**
 * Job state enum.
 *
 * An job goes thru three main phases: pending, started or completed. For
 * details about the possible states in each phase, see getPhase(). The only
 * one-to-one mapping is the pending state.
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
                        case self::WARNING:
                                return "orange";
                        case self::SUCCESS:
                                return "green";
                        case self::PENDING:
                                return "light-grey";
                        case self::RUNNING:
                                return "yellow";
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

        /**
         * Check if status is good.
         * 
         * The status is considered good if the job has not failed fatal (i.e.
         * crached) or ended in error state. This implies that pending jobs and
         * suspended jobs are also good.
         * 
         * @return bool
         */
        public function isGood(): bool
        {
                switch ($this->value) {
                        case self::CRASHED:
                        case self::ERROR:
                                return false;
                        default:
                                return true;
                }
        }

        /**
         * Check if job is completed (successful or with warnings).
         * @return bool
         */
        public function isCompleted(): bool
        {
                switch ($this->value) {
                        case self::SUCCESS:
                        case self::WARNING:
                                return true;
                        default:
                                return false;
                }
        }

        /**
         * Check if job phase is pending.
         * @return bool
         */
        public function isPending(): bool
        {
                return $this->getPhase() == self::PENDING();
        }

        /**
         * Check if job phase is running.
         * @return bool
         */
        public function isStarted(): bool
        {
                return $this->getPhase() == self::RUNNING();
        }

        /**
         * Check if job phase is finished.
         * @return bool
         */
        public function isFinished(): bool
        {
                return $this->getPhase() == self::FINISHED();
        }

        /**
         * Get main phase.
         * 
         * Returns either one of the three main state (pending, running or
         * finished) depending on current job state.
         * 
         * @return self
         * @throws RuntimeException
         */
        public function getPhase(): self
        {
                switch ($this->value) {
                        case self::PENDING:
                        case self::WAITING:
                                return self::PENDING();
                        case self::RUNNING:
                        case self::CONTINUED:
                        case self::RESUMED:
                        case self::SUSPEND:
                                return self::RUNNING();
                        case self::FINISHED:
                        case self::CRASHED:
                        case self::ERROR:
                        case self::SUCCESS:
                        case self::WARNING:
                                return self::FINISHED();
                        default:
                                throw new RuntimeException("Unhandled phase $this->value for job state");
                }
        }

}
