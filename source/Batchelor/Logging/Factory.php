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

namespace Batchelor\Logging;

use Batchelor\Logging\Format\Custom;
use Batchelor\Logging\Format\Dumper;
use Batchelor\Logging\Format\Export;
use Batchelor\Logging\Format\JsonEncode;
use Batchelor\Logging\Format\Serialize;
use Batchelor\Logging\Format\Standard;
use Batchelor\Logging\Special\Buffer;
use Batchelor\Logging\Special\Multiplexer;
use Batchelor\Logging\Special\Request;
use Batchelor\Logging\Target\File;
use Batchelor\Logging\Target\Syslog;
use Batchelor\Logging\Target\Zero;
use InvalidArgumentException;

/**
 * The logger factory.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Factory
{

        /**
         * Get logger object.
         * 
         * The logger is initialized by settings from the options array,
         * including format options if defined. This function will also
         * apply threshold if defined.
         * 
         * @return Writer
         */
        public static function getObject(array $options): Writer
        {
                if (isset($options['format'])) {
                        $logger = self::getLogger($options['type'], $options['options']);
                        $format = self::getFormat($options['format']['type'], $options['format']);
                        $logger->setFormat($format);
                } else {
                        $logger = self::getLogger($options['type'], $options['options']);
                }

                if (isset($options['threshold'])) {
                        $logger->setThreshold($options['threshold']);
                }

                if (isset($options['buffer']) && $options['buffer']) {
                        return new Buffer($logger);
                } else {
                        return $logger;
                }
        }

        /**
         * Create logger object.
         * 
         * @param string $type The logger type (i.e. file).
         * @param array $options The logger options.
         * @return Writer
         * @throws InvalidArgumentException
         */
        public static function getLogger(string $type, array $options): Writer
        {
                switch ($type) {
                        case 'file':
                                return File::create($options);
                        case 'syslog':
                                return Syslog::create($options);
                        case 'multiplex':
                                return Multiplexer::create($options);
                        case 'null':
                        case 'zero':
                                return new Zero();
                        case 'request':
                                return Request::create($options);
                        default:
                                throw new InvalidArgumentException("Unknown logger type $type");
                }
        }

        /**
         * Create format object.
         * 
         * @param string $type The format type.
         * @param array $options The format options.
         * @return Format
         */
        public static function getFormat(string $type, array $options): Format
        {
                switch ($type) {
                        case 'custom':
                                return Custom::create($options);
                        case 'dumper':
                                return Dumper::create($options);
                        case 'export':
                                return Export::create($options);
                        case 'json':
                                return JsonEncode::create($options);
                        case 'serialize':
                                return Serialize::create($options);
                        case 'standard':
                                return Standard::create($options);
                        default:
                                throw new InvalidArgumentException("Unknown fromat type $type");
                }
        }

}
