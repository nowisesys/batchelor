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

/**
 * The datetime formatter.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class DateTime
{

        /**
         * The locale specific format (i.e. "Tue Sep 11 23:11:45 2018").
         */
        const FORMAT_HUMAN = "%c";
        /**
         * The locale specific format (i.e. "09/11/18 23:11:45").
         */
        const FORMAT_LOCALE = "%x %X";
        /**
         * The database ISO format (i.e. "2018-09-11 23:11:45").
         */
        const FORMAT_ISO_DATABASE = "%Y-%m-%d %H:%M:%S";
        /**
         * Format as UNIX epoch timestamp (i.e. "1536700305").
         */
        const FORMAT_UNIX_EPOCH = "%s";
        /**
         * Log with seconds granularity (pseudo format string, same as FORMAT_ISO_DATABASE).
         */
        const FORMAT_SECONDS = "@s";
        /**
         * Log with microseconds granularity (pseudo format string, i.e. "0.51993500 1536700305").
         */
        const FORMAT_MICROSEC = "@m";
        /**
         * Log with microseconds granularity ((pseudo format string, i.e. "1536700305.5206").
         */
        const FORMAT_FLOATSEC = "@f";

        /**
         * The format string.
         * @var string 
         */
        private $_format;

        /**
         * Constructor.
         * 
         * The format string is either a custom one supported by strftime or 
         * one of the FORMAT_XXX constants). The default formatting is to use 
         * ISO database format.
         * 
         * @param string $format The format string.
         * @see strftime()
         */
        public function __construct($format = self::FORMAT_ISO_DATABASE)
        {
                $this->_format = $format;
        }

        /**
         * Set format string.
         * @param string $format The format string.
         */
        public function setFormat(string $format)
        {
                $this->_format = $format;
        }

        /**
         * Get formatted datetime string.
         * @param int $stamp The time stamp.
         * @return string
         */
        public function getString(int $stamp): string
        {
                if (!isset($this->_format)) {
                        return sprintf(self::FORMAT_ISO_DATABASE, $stamp);
                } elseif ($this->_format == self::FORMAT_SECONDS) {
                        return sprintf(self::FORMAT_ISO_DATABASE, $stamp);
                } elseif ($this->_format == self::FORMAT_MICROSEC) {
                        return microtime();
                } elseif ($this->_format == self::FORMAT_FLOATSEC) {
                        return (string) microtime(true);
                } else {
                        return strftime($this->_format, $stamp);
                }
        }

}
