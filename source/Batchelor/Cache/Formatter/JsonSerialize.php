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
use LogicException;
use UnexpectedValueException;

/**
 * Formatter using JSON encode/decode.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class JsonSerialize implements Formatter
{

        /**
         * The read/save options.
         * @var array
         */
        private $_options = [
                'read' => [
                        'assoc'   => false,
                        'depth'   => 512,
                        'options' => 0
                ],
                'save' => [
                        'depth'   => 512,
                        'options' => 0
                ]
        ];

        /**
         * {@inheritdoc}
         */
        public function onRead($value)
        {
                if (!($options = $this->getOptions('read'))) {
                        throw new LogicException("Missing read options for calling json_decode()");
                }

                if ($value == json_encode(false)) {
                        return false;
                }

                if (!($data = json_decode($value, $options['assoc'], $options['depth'], $options['options']))) {
                        throw new UnexpectedValueException("Failed format data using json_decode()");
                } else {
                        return $data;
                }
        }

        /**
         * {@inheritdoc}
         */
        public function onSave($value)
        {
                if (!($options = $this->getOptions('read'))) {
                        throw new LogicException("Missing save options for calling json_encode()");
                }

                if (!($data = json_encode($value, $options['options'], $options['depth']))) {
                        throw new UnexpectedValueException("Failed format data using json_encode()");
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
