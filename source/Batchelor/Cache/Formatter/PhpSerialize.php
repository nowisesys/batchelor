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
use UnexpectedValueException;

/**
 * Formatter using PHP serialize/unserialize.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class PhpSerialize implements Formatter
{

        /**
         * The read/save options.
         * @var array
         */
        private $_options = [];

        /**
         * {@inheritdoc}
         */
        public function onRead($value)
        {
                if (!($options = $this->getOptions('read'))) {
                        $options = [];
                }

                if ($value == serialize(false)) {
                        return false;
                }
                
                if (!($data = unserialize($value, $options))) {
                        throw new UnexpectedValueException("Failed format data using unserialize()");
                } else {
                        return $data;
                }
        }

        /**
         * {@inheritdoc}
         */
        public function onSave($value)
        {
                if (!($data = serialize($value))) {
                        throw new UnexpectedValueException("Failed format data using serialize()");
                } else {
                        return $data;
                }
        }

        /**
         * {@inheritdoc}
         */
        public function setOptions(array $options)
        {
                $this->_options = array_merge($this->_options, $options);
        }

        /**
         * {@inheritdoc}
         */
        public function getOptions(string $func = null)
        {
                if (!isset($func)) {
                        return $this->_options;
                } elseif (isset($this->_options[$func])) {
                        return $this->_options[$func];
                } else {
                        return false;
                }
        }

}
