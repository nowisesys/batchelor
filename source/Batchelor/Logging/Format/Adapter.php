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
 * The formatter adapter.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
abstract class Adapter implements Format
{

        /**
         * The datetime formatter.
         * @var DateTime 
         */
        private $_formatter;

        public function __construct()
        {
                $this->_formatter = new DateTime();
        }

        /**
         * {@inheritdoc}
         */
        public function setDateTime(DateTime $format)
        {
                $this->_formatter = $format;
        }

        /**
         * {@inheritdoc}
         */
        public function getDateTime(): DateTime
        {
                return $this->_formatter;
        }

        /**
         * Get priority string.
         * @param int $priority The log priority.
         */
        protected function getPriority(int $priority): string
        {
                static $priorities = [
                        LOG_EMERG   => "emergency",
                        LOG_ALERT   => "alert",
                        LOG_CRIT    => "critical",
                        LOG_ERR     => "error",
                        LOG_WARNING => "warning",
                        LOG_NOTICE  => "notice",
                        LOG_INFO    => "info",
                        LOG_DEBUG   => "debug"
                ];

                if (array_key_exists($priority, $priorities)) {
                        return $priorities[$priority];
                }
        }

        /**
         * Get datetime string,
         * 
         * @param int $stamp The UNIX timestamp.
         * @return string The formatted datetime string,
         */
        protected function getTimestamp(int $stamp): string
        {
                $formatter = $this->_formatter;
                return $formatter->getString($stamp);
        }

}
