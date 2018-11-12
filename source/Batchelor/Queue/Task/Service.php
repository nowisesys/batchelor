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

/**
 * The task processor service.
 * 
 * <code>
 * // 
 * // Register processor of chemgps tasks:
 * // 
 * $service->setProcessor('chemgps', new TaskChemGPS());
 * </code>
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Service
{

        /**
         * The processor registry.
         * @var array 
         */
        private $_processors = [];

        /**
         * Constructor.
         * @param array $processors The array of processors.
         */
        public function __construct(array $processors = [])
        {
                $this->_processors = $processors;
        }

        /**
         * Register task processor.
         * 
         * The name is some symbolic name for identifying the task runner. If
         * same installation handles different job types, then register processor
         * using this method.
         * 
         * @param string $name The processor name.
         * @param Task $task The task processor.
         */
        public function setProcessor(string $name, Task $task)
        {
                $this->_processors[$name] = $task;
        }

        /**
         * Register default task processor.
         * 
         * This is the default processor returned unless explicit requesting an
         * named processor. If installation only handles one type of task, then
         * this is the prefered method to call.
         * 
         * @param Task $task The task processor.
         */
        public function setDefault(Task $task)
        {
                $this->_processors['default'] = $task;
        }

        /**
         * Check if task processor is registered.
         * 
         * @param string $name The processor name.
         * @return bool
         */
        public function hasProcesor(string $name): bool
        {
                return isset($this->_processors[$name]);
        }

        /**
         * Get task processor.
         * 
         * @param string $name The processor name.
         * @return Task 
         */
        public function getProcessor(string $name): Task
        {
                return $this->_processors[$name];
        }

}
