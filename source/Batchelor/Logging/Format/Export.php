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
 * The PHP variable export formatter.
 * 
 * When context mode is enabled, each log entry is logged decorated with code
 * enable parsing entries as code using i.e. require() or include(). 
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Export extends Adapter implements Format
{

        /**
         * Use compact format.
         * @var bool 
         */
        private $_compact = false;
        /**
         * Add code context.
         * @var bool 
         */
        private $_context = false;

        /**
         * Set compact mode.
         * @param bool $enable Enable compact mode.
         */
        public function setCompact(bool $enable = true)
        {
                $this->_compact = $enable;
        }

        /**
         * Add context to log entry.
         * @param bool $enable Enable context mode.
         */
        public function addContext(bool $enable = true)
        {
                $this->_context = $enable;
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
                if ($this->_context) {
                        return sprintf("'%f' => %s,", microtime(true), var_export($input, true));
                } else {
                        return var_export($input, true);
                }
        }

        /**
         * The format factory function.
         * 
         * @param array $options The format options.
         * @return Format
         */
        public static function create(array $options): Format
        {
                $format = new Export();

                if (isset($options['datetime'])) {
                        $format->getDateTime()->setFormat($options['datetime']);
                }
                if (isset($options['context'])) {
                        $format->addContext($options['context']);
                }
                if (isset($options['compact'])) {
                        $format->setCompact($options['compact']);
                }

                return $format;
        }

}
