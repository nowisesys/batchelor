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

namespace Batchelor\Cache\Formatter;

use Batchelor\Cache\Formatter;

/**
 * The default formatter.
 * 
 * This formatter is simply not applying any formatting on read/save. Values are 
 * passed unmodified, thus native format being used as class name. It's intended
 * to be used as a noop pluging for a real formatter. No options are accepted for
 * this formatter.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class NativeFormat implements Formatter
{

        /**
         * {@inheritdoc}
         */
        public function onRead($value)
        {
                return $value;
        }

        /**
         * {@inheritdoc}
         */
        public function onSave($value)
        {
                return $value;
        }

        /**
         * This method is ignored.
         * @param array $options The options to set.
         */
        public function setOptions(array $options)
        {
                // Ignore
        }

        /**
         * This method is ignored.
         * @param string $func Either null, read or save.
         * @return boolean
         */
        public function getOptions(string $func = null)
        {
                return false;
        }

}
