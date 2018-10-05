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

namespace Batchelor\Queue\Task\Scheduler\State;

use Batchelor\WebService\Types\JobIdentity;
use Batchelor\WebService\Types\JobState;

/**
 * The state migrator.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Migration
{

        /**
         * The source mutator.
         * @var Mutator 
         */
        private $_source;
        /**
         * The destination mutator.
         * @var Mutator 
         */
        private $_target;

        /**
         * Set source mutator.
         * 
         * @param Mutator $mutator
         * @return Migration
         */
        public function setSource(Mutator $mutator): Migration
        {
                $this->_source = $mutator;
                return $this;
        }

        /**
         * Set destination mutator.
         * 
         * @param Mutator $mutator
         * @return Migration
         */
        public function setTarget(Mutator $mutator): Migration
        {
                $this->_target = $mutator;
                return $this;
        }

        /**
         * Migrate identity between mutators.
         * 
         * Moves the identity from source mutator to destination mutator. The 
         * status is updated during the move. If source and destination is same
         * mutator, then the status is just updated.
         * 
         * @param JobIdentity $identity The job identity.
         * @param JobState $state The job state.
         */
        public function moveTo(JobIdentity $identity, JobState $state): Migration
        {
                $status = $this->_source->getStatus($identity);
                $status->state = $state->getValue();

                if ($this->_source == $this->_target) {
                        $this->_source->setStatus($identity, $status);
                } else {
                        $this->_source->removeStatus($identity);
                        $this->_target->addStatus($identity, $status);
                }

                return $this;
        }

}
