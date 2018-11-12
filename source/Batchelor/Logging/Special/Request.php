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

namespace Batchelor\Logging\Special;

use Batchelor\Logging\Target\File;
use Batchelor\Logging\Writer;

/**
 * The request logger.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Request extends File
{

        /**
         * Constructor.
         * 
         * @param string $path The target directory.
         * @param string $ident The string ident is added to each message.
         */
        public function __construct(string $path, string $ident = "batchelor")
        {
                $hostname = self::getHostname();
                $filename = sprintf("%s/%s-%s.log", $path, $ident, $hostname);

                parent::__construct($filename, $ident);
        }

        /**
         * Get hostname from peer.
         * @return string
         */
        private static function getHostname(): string
        {
                if (!($hostname = self::getRemote())) {
                        return "unknown";
                }
                if ($hostname == "::1") {
                        return "localhost";
                } else {
                        return $hostname;
                }
        }

        /**
         * Get remote host or address.
         * @return string
         */
        private static function getRemote()
        {
                if (filter_has_var(INPUT_SERVER, 'REMOTE_HOST')) {
                        return filter_input(INPUT_SERVER, 'REMOTE_HOST');
                }
                if (filter_has_var(INPUT_SERVER, 'REMOTE_ADDR')) {
                        return filter_input(INPUT_SERVER, 'REMOTE_ADDR');
                }
        }

        /**
         * The request logger factory function.
         * 
         * @param array $options The logger options.
         * @return Writer
         */
        public static function create(array $options): Writer
        {
                if (!isset($options['path'])) {
                        $options['path'] = sys_get_temp_dir();
                }
                if (!isset($options['ident'])) {
                        $options['ident'] = 'batchelor';
                }

                return new Request($options['path'], $options['ident']);
        }

}
