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

/**
 * The log writer interface.
 * @author Anders Lövgren (Nowise Systems)
 */
interface Writer
{

        /**
         * Get formatted message.
         * 
         * @param string $message The message to log.
         * @param array $args Optional arguments for message.
         * @return array The formatted message.
         */
        function getMessage(string $message, array $args = []): array;

        /**
         * Set message formatter.
         * @param Format $format The message formatter.
         */
        function setFormat(Format $format);

        /**
         * Get message formatter.
         * @return Format The message formatter.
         */
        function getFormat(): Format;

        /**
         * Set message importance threshold.
         * 
         * Messages with priority higher or equal to the given priority are
         * silently discarded from logging. Notice that LOG_DEBUG is highest
         * and LOG_EMERG is lowest priority.
         * 
         * @param int $priority The message priority.
         */
        function setThreshold(int $priority);
}
