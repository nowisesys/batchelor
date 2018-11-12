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
 * Gecos for submitted job.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class JobSubmit
{

        /**
         * The task name.
         * @var string 
         */
        public $task;
        /**
         * The optional job name.
         * @var string 
         */
        public $name;

        /**
         * Constructor.
         * @param string $task The task name.
         * @param string $name The optional job name.
         */
        public function __construct(string $task = 'default', string $name = null)
        {
                $this->task = $task;
                $this->name = $name;
        }

}
