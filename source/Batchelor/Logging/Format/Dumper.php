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
 * The PHP variable dump formatter.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Dumper extends Adapter implements Format
{

        /**
         * Use compact format.
         * @var bool 
         */
        private $_compact = false;
        /**
         * Add message context.
         * @var bool 
         */
        private $_context = false;
        /**
         * The process user ID.
         * @var int 
         */
        private $_uid = 0;
        /**
         * The process group ID.
         * @var int 
         */
        private $_gid = 0;
        /**
         * The process current working directory.
         * @var string 
         */
        private $_cwd = "";

        public function __construct()
        {
                parent::__construct();

                if (extension_loaded("posix")) {
                        $this->_uid = posix_getuid();
                        $this->_gid = posix_getgid();
                        $this->_cwd = posix_getcwd();
                }
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
         * Add context to log entry.
         * @param bool $enable Enable context mode.
         */
        public function addContext(bool $enable = true)
        {
                $this->_context = $enable;
                $this->_compact = $enable ? false : $this->_compact;
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
                        $input = [
                                'priority' => $input['priority'],
                                'occasion' => [
                                        'datetime' => $input['datetime'],
                                        'stamp'    => $input['stamp'],
                                ],
                                'process'  => [
                                        'ident' => $input['ident'],
                                        'pid'   => $input['pid'],
                                        'uid'   => $this->_uid,
                                        'gid'   => $this->_gid,
                                        'cwd'   => $this->_cwd
                                ],
                                'message'  => $input['message']
                        ];
                }

                return print_r($input, true);
        }

        /**
         * The format factory function.
         * 
         * @param array $options The format options.
         * @return Format
         */
        public static function create(array $options): Format
        {
                $format = new Dumper();

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
