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

use RuntimeException;

/**
 * The syslog class.
 * 
 * <code>
 * // 
 * // Pass identity at minimum:
 * // 
 * $logger = new Syslog("batchelor");
 * $logger->info("hello world!");
 * 
 * // 
 * // Custom options can be passed too:
 * // 
 * $logger = new Syslog("batchelor", LOG_PID | LOG_PERROR, LOG_DAEMON);
 * </code>
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Syslog extends Adapter implements Logger
{

        /**
         * Constructor.
         * 
         * See the syslog documentation for possible values for options and
         * facility arguments. The default is use LOG_CONS | LOG_PID for
         * options and LOG_USER for facility.
         * 
         * @param string $ident The string ident is added to each message.
         * @param int $option The option argument is used to indicate what logging options will be used when generating a log message.
         * @param int $facility The facility argument is used to specify what type of program is logging the message.
         * @throws RuntimeException
         */
        public function __construct(string $ident, int $option = LOG_CONS | LOG_PID, int $facility = LOG_USER)
        {
                if (!openlog($ident, $option, $facility)) {
                        throw new RuntimeException("Failed open syslog");
                }
        }

        /**
         * Destructor.
         * @throws RuntimeException
         */
        public function __destruct()
        {
                if (!closelog()) {
                        throw new RuntimeException("Failed close syslog");
                }
        }

        /**
         * {@inheritdoc}
         */
        public function message(int $priority, string $message, array $args = array()): bool
        {
                return syslog($priority, vsprintf($message, $args));
        }

        /**
         * {@inheritdoc}
         */
        public function getMessage(string $message, array $args = array()): array
        {
                return [
                        'stamp'   => microtime(true),
                        'message' => vsprintf($message, $args)
                ];
        }

}
