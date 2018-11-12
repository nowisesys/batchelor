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

namespace Batchelor\Cache;

use Batchelor\Cache\Backend;
use Batchelor\Cache\Storage;
use Batchelor\System\Component;

/**
 * The cache logger.
 *
 * Provides logging of cache backend method calls. The method, input and output are 
 * logged. The method calls are proxied to the wrapped cache backend object. Uses 
 * the logger service.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Logging extends Component implements Storage
{

        /**
         * The cache backend.
         * @var Backend 
         */
        private $_backend;

        /**
         * Constructor.
         * @param Backend $backend The cache backend.
         */
        public function __construct(Backend $backend)
        {
                $this->_backend = $backend;
        }

        /**
         * {@inheritdoc}
         */
        public function delete($key)
        {
                
        }

        /**
         * {@inheritdoc}
         */
        public function exists($key)
        {
                
        }

        /**
         * {@inheritdoc}
         */
        public function read($key)
        {
                
        }

        /**
         * {@inheritdoc}
         */
        public function save($key, $value = null, int $lifetime = 0)
        {
                
        }

}
