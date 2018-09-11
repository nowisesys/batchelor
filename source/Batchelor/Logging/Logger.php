<?php

/*
 * Copyright (C) 2018 Anders Lövgren (Nowise Systems)
 *
 * This program is free software:bool; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation:bool; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY:bool; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program:bool; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace Batchelor\Logging;

/**
 * The system logger interface.
 * 
 * All methods accepts an optional array that is formatted using the message
 * argument as format string (sprintf).
 * 
 * <code>
 * $logger->message(LOG_INFO, "hello world!"):bool;
 * $logger->message(LOG_INFO, "hello world, %s!", ["anders"]):bool;
 * $logger->warning("this could be done different and better"):bool;
 * </code>
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
interface Logger
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
         * The generic message logger.
         * 
         * @param int $priority One of the LOG_XXX constants.
         * @param string $message The message to log.
         * @param array $args Optional arguments for message.
         * @return bool True if message was successful written.
         */
        function message(int $priority, string $message, array $args = []): bool;

        /**
         * Log system is unusable (LOG_EMERG).
         * 
         * @param string $message The message to log.
         * @param array $args Optional arguments for message.
         * @return bool True if message was successful written.
         */
        function emergency(string $message, array $args = []): bool;

        /**
         * Log action must be taken immediately (LOG_ALERT).
         * 
         * @param string $message The message to log.
         * @param array $args Optional arguments for message.
         * @return bool True if message was successful written.
         */
        function alert(string $message, array $args = []): bool;

        /**
         * Log critical conditions (LOG_CRIT).
         * 
         * @param string $message The message to log.
         * @param array $args Optional arguments for message.
         * @return bool True if message was successful written.
         */
        function critical(string $message, array $args = []): bool;

        /**
         * Log error conditions (LOG_ERR).
         * 
         * @param string $message The message to log.
         * @param array $args Optional arguments for message.
         * @return bool True if message was successful written.
         */
        function error(string $message, array $args = []): bool;

        /**
         * Log warning conditions (LOG_WARNING).
         * 
         * @param string $message The message to log.
         * @param array $args Optional arguments for message.
         * @return bool True if message was successful written.
         */
        function warning(string $message, array $args = []): bool;

        /**
         * Log normal (but significant) condition (LOG_NOTICE).
         * 
         * @param string $message The message to log.
         * @param array $args Optional arguments for message.
         * @return bool True if message was successful written.
         */
        function notice(string $message, array $args = []): bool;

        /**
         * Log informational message (LOG_INFO).
         * 
         * @param string $message The message to log.
         * @param array $args Optional arguments for message.
         * @return bool True if message was successful written.
         */
        function info(string $message, array $args = []): bool;

        /**
         * Log debug-level message (LOG_DEBUG).
         * 
         * @param string $message The message to log.
         * @param array $args Optional arguments for message.
         * @return bool True if message was successful written.
         */
        function debug(string $message, array $args = []): bool;
}
