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

namespace Batchelor\System\Service;

use Batchelor\Cache\Frontend;
use Batchelor\Cache\Storage;
use Batchelor\System\Component;

/**
 * The cache service.
 * 
 * <code>
 * $cache = new Cache();        // Use application settings
 * $cache = new Cache([         // Set Redis as cache backend.
 *      'type' => 'redis',
 *      'options' => [ ... ]
 *      ]
 * ])
 * </code>
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Cache extends Component implements Storage
{

        /**
         * The cache frontend.
         * @var Frontend 
         */
        private $_frontend;

        /**
         * Constructor.
         * 
         * @param array $options The frontend options.
         */
        public function __construct(array $options = null)
        {
                if (!isset($options)) {
                        $options = $this->app->cache->getArrayCopy();
                }

                if (!isset($options['type'])) {
                        $options['type'] = null;
                }
                if (!isset($options['options'])) {
                        $options['options'] = [];
                }

                foreach ($options as $key => $val) {
                        if (is_object($val)) {
                                $options[$key] = $val->getArrayCopy();
                        }
                }

                $this->_frontend = new Frontend($options['type'], $options['options']);
        }

        /**
         * Get cache frontend.
         * @return Frontend The cache frontend.
         */
        public function getFrontend(): Frontend
        {
                return $this->_frontend;
        }

        /**
         * {@inheritdoc}
         */
        public function delete($key)
        {
                $frontend = $this->_frontend;
                return $frontend->delete($key);
        }

        /**
         * {@inheritdoc}
         */
        public function exists($key)
        {
                $frontend = $this->_frontend;
                return $frontend->exists($key);
        }

        /**
         * {@inheritdoc}
         */
        public function read($key)
        {
                $frontend = $this->_frontend;
                return $frontend->read($key);
        }

        /**
         * {@inheritdoc}
         */
        public function save($key, $value = null, int $lifetime = 0)
        {
                $frontend = $this->_frontend;
                return $frontend->save($key, $value, $lifetime);
        }

}
