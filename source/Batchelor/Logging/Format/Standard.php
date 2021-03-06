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

namespace Batchelor\Logging\Format;

use Batchelor\Logging\Format;

/**
 * The standard message formatter.
 * 
 * Generates log messages using a fixed message format string (%s [%s:%d] [%s] %s)
 * formatting the stamp, ident, pid, priority and message argument passed in using 
 * the input array. 
 * 
 * The priority are replaced by its symbolic name string and stamp are formatted
 * using the active datetime formatter.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Standard extends Adapter implements Format
{

        /**
         * {@inheritdoc}
         */
        public function getMessage(array $input): string
        {
                $input['priority'] = parent::getPriority($input['priority']);
                $input['datetime'] = parent::getTimestamp($input['stamp']);

                return sprintf(
                    "%s [%s:%d] [%s] %s", $input['datetime'], $input['ident'], $input['pid'], $input['priority'], $input['message']
                );
        }

        /**
         * The format factory function.
         * 
         * @param array $options The format options.
         * @return Format
         */
        public static function create(array $options): Format
        {
                $format = new JsonEncode();

                if (isset($options['datetime'])) {
                        $format->getDateTime()->setFormat($options['datetime']);
                }

                return $format;
        }

}
