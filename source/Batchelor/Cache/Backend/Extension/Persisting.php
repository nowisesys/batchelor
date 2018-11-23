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

namespace Batchelor\Cache\Backend\Extension;

/**
 * Persisting cache backend.
 * 
 * This is a pseudo-backend that detects available backends and uses the first 
 * detected that supports persiting storage as its backend. The options are fixed
 * to use lifetime == 0 (cache entries never expires).
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Persisting extends Detected
{

        /**
         * Constructor.
         * @param array $options The backend options.
         * @param array $exclude The backends to exclude.
         */
        public function __construct(array $options = [], array $exclude = [])
        {
                if ($this->isExpiring($options)) {
                        $this->logger->system->warning("The lifetime for persiting cache entries is not 0 (was corrected, but please check)");
                }

                parent::__construct(
                    $options, array_unique(
                        array_merge($exclude, [
                        "apcu", "xcache", "memory", "memcached", "shmop"
                            ]
                        )
                    )
                );
        }

        /**
         * Check if cache entries will expire.
         * 
         * @param array $options The cache options.
         * @return bool 
         */
        private function isExpiring(array &$options): bool
        {
                if (!isset($options['lifetime'])) {
                        return false;
                } elseif ($options['lifetime'] == 0) {
                        return false;
                }

                $options['lifetime'] = 0;
                return true;
        }

}
