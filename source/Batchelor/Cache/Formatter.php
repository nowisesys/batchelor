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
 * The input/output formatter.
 * 
 * An concrete class can implement this interface to provide formatting
 * of values saved to cache or read from cache.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
interface Formatter
{

        /**
         * Set formatter specific options.
         * 
         * <code>
         * $formatter = new JSon();
         * $formatter->setOptions(['read' => ['assoc' => true]]);       // Set JSON read option
         * </code>
         * 
         * @param array $options The options to set.
         */
        function setOptions(array $options);

        /**
         * Get formatter specific options.
         * 
         * Return false if options are unset or if options for given func
         * is missing.
         * 
         * @param string $func If used, either read or save.
         * @return boolean|array
         */
        function getOptions(string $func = null);

        /**
         * Applied before save to cache.
         * 
         * @param mixed $value The value to encode.
         * @return mixed
         * @throws UnexpectedValueException|LogicException
         */
        function onSave($value);

        /**
         * Applied after read from cache.
         * 
         * @param mixed $value The value to decode.
         * @return mixed
         * @throws UnexpectedValueException|LogicException
         */
        function onRead($value);
}
