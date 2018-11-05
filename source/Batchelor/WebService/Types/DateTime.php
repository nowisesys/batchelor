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

use DateTime as StandardDateTime;
use LogicException;
use Serializable;

/**
 * The datetime class.
 * 
 * This class is a support class for WSDL generation. It's not required or used 
 * for SOAP request/repsonse, nor is it serialized. The public properties exposed 
 * by this class should have the same name as those exposed by the intrinsic 
 * datetime object (that is actually used as SOAP parameters).
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class DateTime extends StandardDateTime implements Serializable
{

        /**
         * The datetime string.
         * @var string 
         */
        public $date;
        /**
         * The timezone type (i.e. 3).
         * @var int
         */
        public $timezone_type;
        /**
         * The timezone.
         * @var string 
         */
        public $timezone;

        /**
         * Guard against being serialized.
         * @throws LogicException
         */
        public function serialize(): string
        {
                throw new LogicException("This class should not be serialized");
        }

        /**
         * Guard against being unserialized.
         * @throws LogicException
         */
        public function unserialize($serialized): void
        {
                throw new LogicException("This class should not be unserialized");
        }

}
