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

namespace Batchelor\Queue\Task\Scheduler;

use Batchelor\Cache\Storage;

/**
 * Counter for state queue.
 * 
 * This is a simple class that helps keeping track on the state queue size
 * without actually open the queue itself.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Counter
{

        /**
         * The queue name.
         * @var string 
         */
        private $_ident;
        /**
         * The cache backend.
         * @var Storage 
         */
        private $_cache;

        /**
         * Constructor.
         * 
         * @param string $ident The queue name.
         * @param Storage $cache The cache backend.
         */
        public function __construct(string $ident, Storage $cache)
        {
                $this->_ident = $ident;
                $this->_cache = $cache;
        }

        public function increment()
        {
                $value = $this->getValue();
                $this->setValue( ++$value);
        }

        public function decrement()
        {
                $value = $this->getValue();
                $this->setValue( --$value);
        }

        public function getSize(): int
        {
                return $this->getValue();
        }

        public function setSize(int $size)
        {
                $this->setValue($size);
        }

        private function getValue()
        {
                $cname = $this->getCacheKey();
                $cache = $this->_cache;

                if ($cache->exists($cname)) {
                        return $cache->read($cname);
                } else {
                        return 0;
                }
        }

        private function setValue($value)
        {
                if ($value < 0) {
                        return;
                }

                $cname = $this->getCacheKey();
                $cache = $this->_cache;

                $cache->save($cname, $value);
        }

        private function getCacheKey(): string
        {
                return sprintf("scheduler-%s-count", $this->_ident);
        }

}
