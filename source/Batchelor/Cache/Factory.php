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

namespace Batchelor\Cache;

use Batchelor\Cache\Backend\APCu;
use Batchelor\Cache\Backend\File;
use Batchelor\Cache\Formatter\JsonSerialize;
use Batchelor\Cache\Formatter\NativeFormat;
use Batchelor\Cache\Formatter\PhpSerialize;
use LogicException;

/**
 * Creates cache backend.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Factory
{

        /**
         * Get cache backend.
         * 
         * @param string $type The backend type.
         * @param array $options The backend options.
         * @return Backend
         */
        public static function getBackend(string $type, array $options = [])
        {
                // TODO: implement

                switch ($type) {
                        case 'apcu':
                                return new APCu($options);
                        case 'file':
                                return new File($options);
                        default:
                                throw new LogicException("Unknown cache backend $type");
                }
        }

        /**
         * Get read/save formatter.
         * @param string $type The format name.
         * @return Formatter
         */
        public static function getFormatter(string $type)
        {
                switch ($type) {
                        case 'native':
                                return new NativeFormat();
                        case 'php':
                                return new PhpSerialize();
                        case 'json':
                                return new JsonSerialize();
                        default:
                                throw new LogicException("Unknown read/save format type $type");
                }
        }

}
