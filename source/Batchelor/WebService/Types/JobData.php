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
 * The job data (indata) class.
 * 
 * Represent data used as input for an scheduled job. The job data can be plain 
 * data or an download URL. 
 * 
 * To keep things simple (at least for now) we assume that the download URL will 
 * send the data in a format not requiring further processing. At this time we 
 * are not supporting sending credentials for authentication. 
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class JobData
{

        /**
         * The job data (plain data or an URL).
         * @var string 
         */
        public $data;
        /**
         * The data type (either "data" or "url").
         * @var string 
         */
        public $type;

        /**
         * Constructor.
         * @param string $data The job data (plain data or an URL).
         * @param string $type The data type (either "data" or "url").
         */
        public function __construct(string $data, string $type)
        {
                $this->data = $data;
                $this->type = $type;
        }

}
