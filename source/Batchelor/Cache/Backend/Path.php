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

namespace Batchelor\Cache\Backend;

/**
 * The path cache.
 * 
 * Adds support for saving cache entry in sub directory tree. How to define the
 * sub directory path is user defined. Defaults is to split on first '-' in cache
 * key name.
 * 
 * Examples:
 * 
 * 1. The cache key name might be used to generate a cache entry named in sub
 *    directory path: "a-b-c" -> "a/b/c".
 * 
 * 2. Extract two sub string from the cache key name used to generate index
 *    sub tree: "ebd8e0a4777cb9a8b" -> "e/ebd/ebd8e0a4777cb9a8b".
 * 
 * <code>
 * $cache->setExtractor(function($key) {
 *      return sprintf("%s/%s/%s", substr($key, 0, 1), substr($key, 0, 3), $key);
 * });
 * </code>
 *
 * The sub directory path is automatic constructed.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Path extends File
{

        /**
         * The cache key extractor.
         * @var callback 
         */
        private $_extractor;

        /**
         * {@inheritdoc}
         */
        public function __construct($options = array())
        {
                parent::__construct($options);

                $this->_extractor = function($key) {
                        if (($pos = strpos($key, "-")) == false) {
                                return $key;
                        } else {
                                return substr_replace($key, "/", $pos, 1);
                        }
                };
        }

        /**
         * Set cache key extractor.
         * 
         * @param callable $extractor The cache key extractor.
         */
        public function setExtractor(callable $extractor)
        {
                $this->_extractor = $extractor;
        }

        /**
         * {@inheritdoc}
         */
        public function getCacheKey(string $key): string
        {
                if (!($key = parent::getCacheKey($key))) {
                        return $key;
                } elseif (($extractor = $this->_extractor)) {
                        return $extractor($key);
                } else {
                        return $key;
                }
        }

}
