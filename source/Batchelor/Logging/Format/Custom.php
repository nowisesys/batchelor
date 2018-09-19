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
 * The custom message formatter.
 * 
 * Supports custom formatting of log messages by setting an variable expansion 
 * string defining the layout of each log message:
 * 
 * <code>
 * // 
 * // Using this message expansion string would format sub sequent messages
 * // i.e. as "2018-09-11 23:52:48 <info><32100> some message text..."
 * // 
 * $custom->setExpand("@datetime@ <@priority@><@pid@> @message@");
 * $custom->getMessage($input);
 * </code>
 * 
 * The default message format is equivalent to the standard formatter, but can be
 * changed by setting an expansion string using the available substitution strings:
 * 
 * @stamp@, @datetime@, @ident@, @pid@, @priority@, @message@
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Custom extends Adapter implements Format
{

        /**
         * The expand string.
         * @var string 
         */
        private $_expand = "@datetime@ [@ident@:@pid@] [@priority@] @message@";

        /**
         * Set variable expansion string.
         * @param string $expand The expansion string.
         */
        public function setExpand(string $expand)
        {
                $this->_expand = $expand;
        }

        /**
         * {@inheritdoc}
         */
        public function getMessage(array $input): string
        {
                $input['priority'] = parent::getPriority($input['priority']);
                $input['datetime'] = parent::getTimestamp($input['stamp']);

                $search = [
                        "@stamp@"    => $input['stamp'],
                        "@datetime@" => $input['datetime'],
                        "@ident@"    => $input['ident'],
                        "@pid@"      => $input['pid'],
                        "@priority@" => $input['priority'],
                        "@message@"  => $input['message']
                ];

                return str_replace(
                    array_keys($search), array_values($search), $this->_expand
                );
        }

        /**
         * The format factory function.
         * 
         * @param array $options The format options.
         * @return Format
         */
        public static function create(array $options): Format
        {
                $format = new Custom();

                if (isset($options['datetime'])) {
                        $format->getDateTime()->setFormat($options['datetime']);
                }
                if (isset($options['expand'])) {
                        $format->setExpand($options['expand']);
                }

                return $format;
        }

}
