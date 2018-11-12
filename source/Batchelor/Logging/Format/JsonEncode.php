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
 * The JSON formatter.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class JsonEncode extends Adapter implements Format
{

        /**
         * The JSON encode options.
         * @var int 
         */
        private $_options = 0;
        /**
         * Use compact format.
         * @var bool 
         */
        private $_compact = false;

        /**
         * Set JSON encode options.
         * @param int $options The encode options.
         */
        public function setOptions(int $options)
        {
                $this->_options = $options;
        }

        /**
         * Set compact mode.
         * @param bool $enable Enable compact mode.
         */
        public function setCompact(bool $enable = true)
        {
                $this->_compact = $enable;
        }

        /**
         * {@inheritdoc}
         */
        public function getMessage(array $input): string
        {
                if ($this->_compact == false) {
                        $input['priority'] = parent::getPriority($input['priority']);
                        $input['datetime'] = parent::getTimestamp($input['stamp']);
                }

                return json_encode($input, $this->_options);
        }

        /**
         * The format factory function.
         * 
         * @param array $options The format options.
         * @return Format
         */
        public static function create(array $options): Format
        {
                $format = new JsonEncode();

                if (isset($options['datetime'])) {
                        $format->getDateTime()->setFormat($options['datetime']);
                }
                if (isset($options['options'])) {
                        $format->setOptions($options['options']);
                }
                if (isset($options['compact'])) {
                        $format->setCompact($options['compact']);
                }

                return $format;
        }

}
