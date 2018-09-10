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

/**
 * Cache backend interface.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
interface Backend extends Storage
{

        /**
         * Get read/save formatter.
         * @param Formatter The formatter object.
         */
        function getFormatter(): Formatter;

        /**
         * Set read/save formatter.
         * @param Formatter $formatter The formatter object.
         */
        function setFormatter(Formatter $formatter);

        /**
         * Set cache key prefix.
         * @param string $prefix The prefix name.
         */
        function setPrefix(string $prefix);

        /**
         * Set default cache key lifetime.
         * @param int $lifetime The key lifetime.
         */
        function setLifetime(int $lifetime);

        /**
         * Get config options.
         * @return array The active options.
         */
        function getOptions(): array;

        /**
         * Get config option.
         * 
         * @param string $name The option name.
         * @param mixed $default The default value.
         * @return mixed 
         */
        function getOption(string $name, $default = false);

        /**
         * Get cache key.
         * @param string $key The key name.
         * @return string The prefixed cache key.
         */
        function getCacheKey(string $key): string;
}
