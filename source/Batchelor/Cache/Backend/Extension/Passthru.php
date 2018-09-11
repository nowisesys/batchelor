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

use Batchelor\Cache\Backend\Base;
use Batchelor\Cache\Storage;

/**
 * The passthru cache.
 * 
 * This is simply a drop-in class that refuses to cache anything. It returns
 * false for all key checks.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Passthru extends Base implements Storage
{

        /**
         * Constructor.
         * @param array $options The options array (ignored).
         */
        public function __construct(array $options = [])
        {
                parent::__construct($options, []);
        }

        /**
         * {@inheritdoc}
         */
        public function delete($key)
        {
                return $this->response($key, true);
        }

        /**
         * {@inheritdoc}
         */
        public function exists($key)
        {
                return $this->response($key, false);
        }

        /**
         * {@inheritdoc}
         */
        public function read($key)
        {
                return $this->response($key, null);
        }

        /**
         * {@inheritdoc}
         */
        public function save($key, $value = null, int $lifetime = 0)
        {
                return $this->response($key, false);
        }

        /**
         * Get response value.
         * 
         * @param string|array $key The cache key.
         * @param bool $value The response value.
         * @return string|array
         */
        private function response($key, $value)
        {
                if (is_string($key)) {
                        return $value;
                }
                if (is_array($key)) {
                        return array_fill(0, count($key), $value);
                }
        }

}
