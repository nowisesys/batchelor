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

namespace Batchelor\Queue\Task\Manager;

use Batchelor\Queue\Task\Manager;
use Batchelor\Queue\Task\Runtime;
use RuntimeException;

/**
 * POSIX threaded task executor.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Threads implements Manager
{

        public function __construct(int $workers)
        {
                if (!extension_loaded("pthreads")) {
                        throw new RuntimeException("The pthreads extension is not loaded");
                }
        }

        public function addJob(Runtime $runtime)
        {
                
        }

        public function getChildren(): array
        {
                
        }

        public function isBusy(): bool
        {
                
        }

        public function isIdle(): bool
        {
                
        }

        public function setWorkers(int $number)
        {
                
        }

}
