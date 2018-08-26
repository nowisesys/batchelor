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

namespace Batchelor\System\Persistance;

use Batchelor\System\Persistance\Storage\CookieStorage;
use Batchelor\System\Persistance\Storage\StorageService;

/**
 * The cookie class.
 * 
 * Use this class to create cookies. 
 * 
 * <code>
 * $cookie = new Cookie($name, $value);                         // Create session cookie
 * $cookie = new Cookie($name, $value, time() + 604800);        // Cookie expires in one week
 * 
 * $cookie->save();                     // Send cookie to client
 * $cookie->save($services->cookie);    // Use cookie storage service
 * </code>
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Cookie implements Persistable
{

        /**
         * The cookie name.
         * @var string 
         */
        private $_name;
        /**
         * The cookie value.
         * @var string 
         */
        private $_value;
        /**
         * The cookie lifetime.
         * @var int 
         */
        private $_lifetime;

        /**
         * Constructor.
         * 
         * @param string $name The cookie name.
         * @param string $value The cookie value.
         * @param int $lifetime The cookie lifetime.
         */
        public function __construct($name, $value, $lifetime = 0)
        {
                $this->_name = $name;
                $this->_value = $value;
                $this->_lifetime = $lifetime;
        }

        public function getLifetime(): int
        {
                return $this->_lifetime;
        }

        public function getName(): string
        {
                return $this->_name;
        }

        public function getValue(): string
        {
                return $this->_value;
        }

        public function save(StorageService $service = null)
        {
                if (isset($service)) {
                        $this->persist($service);
                } else {
                        $this->persist(new CookieStorage());
                }
        }

        private function persist(StorageService $service)
        {
                $service->save($this->_name, $this->_value, $this->_lifetime);
        }

}
