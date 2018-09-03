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

use ArrayAccess;
use Batchelor\Cache\Backend;
use Batchelor\Cache\Command\Delete;
use Batchelor\Cache\Command\Exists;
use Batchelor\Cache\Command\Read;
use Batchelor\Cache\Command\Save;
use Serializable;

/**
 * An memory entry.
 */
class MemoryDataEntry
{

        /**
         * The cache value.
         * @var mixed 
         */
        public $value;
        /**
         * The timestamp.
         * @var int 
         */
        public $stamp;

        /**
         * Constructor.
         * @param mixed $value The cache value.
         * @param int $stamp The timestamp.
         */
        public function __construct($value, int $stamp)
        {
                $this->value = $value;
                $this->stamp = $stamp;
        }

        /**
         * Check if entry is valid.
         * @param int $lifetime The cache lifetime.
         * @return boolean
         */
        public function isValid(int $lifetime = 0)
        {
                if ($lifetime == 0) {
                        return true;
                } elseif ($this->hasExpired($lifetime)) {
                        return false;
                } else {
                        return true;
                }
        }

        /**
         * Check if entry has expired.
         * @param int $lifetime The cache lifetime.
         * @return boolean
         */
        public function hasExpired(int $lifetime)
        {
                return $this->stamp > time() - $lifetime;
        }

}

/**
 * The system memory cache.
 * 
 * Provides a cache backend using RAM-memory. Using lifetime on cache keys are fully
 * supported because this backend might be used in CLI-mode. The array access interface
 * is also implemented for unchecked simple/fast access.
 * 
 * The serialize interface is implemented, The formatted is unused for normal cache
 * operations, but used for serialization. 
 * 
 * The performance is comparable to APC using standard methods (i.e. exist() or read()), 
 * but ~3-10 times faster using array access. The downside is that this interface can
 * only be used with single keys. For bulk mode, using save(array) is still faster that
 * using array access.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Memory extends Base implements Backend, Serializable, ArrayAccess
{

        /**
         * The cached entries.
         * @var MemoryDataEntry 
         */
        private $_cached = [];

        /**
         * Constructor.
         * @param array $options The cache options.
         */
        public function __construct($options = [])
        {
                parent::__construct($options, [
                        'format'   => 'json',
                        'prefix'   => 'batchelor',
                        'lifetime' => 0
                ]);
        }

        /**
         * Get cache entry.
         * @param string $key The cache key.
         * @return MemoryDataEntry
         */
        private function getEntry(string $key)
        {
                if (isset($this->_cached[$key])) {
                        return $this->_cached[$key];
                }
        }

        /**
         * Set cache entry.
         * @param string $key The cache key.
         * @param mixed $val The cache value.
         */
        private function setEntry(string $key, $val)
        {
                return $this->_cached[$key] = new MemoryDataEntry($val, time());
        }

        /**
         * Clear cache entry.
         * @param string $key The cache key.
         */
        private function clearEntry(string $key)
        {
                unset($this->_cached[$key]);
        }

        /**
         * {@inheritdoc}
         */
        public function delete($key)
        {
                $command = new Delete($this, $key);
                $command->applyAll(function($keys) {
                        foreach (array_keys($keys) as $key) {
                                $this->clearEntry($key);
                                $keys[$key] = true;
                        }
                        return $keys;
                });

                return $command->getResult(is_string($key));
        }

        /**
         * {@inheritdoc}
         */
        public function exists($key, int $lifetime = 0)
        {
                $command = new Exists($this, $key);
                $command->applyAll(function($keys) use($lifetime) {
                        foreach (array_keys($keys) as $key) {
                                if ($entry = $this->getEntry($key)) {
                                        if ($entry->isValid($lifetime)) {
                                                $keys[$key] = true;
                                        }
                                }
                        }
                        return $keys;
                });

                return $command->getResult(is_string($key));
        }

        /**
         * {@inheritdoc}
         */
        public function read($key, int $lifetime = 0)
        {
                $command = new Read($this, $key);
                $command->applyAll(function($keys) use($lifetime) {
                        foreach (array_keys($keys) as $key) {
                                if ($entry = $this->getEntry($key)) {
                                        if ($entry->isValid($lifetime)) {
                                                $keys[$key] = $entry->value;
                                        }
                                }
                        }
                        return $keys;
                });

                return $command->getResult();
        }

        /**
         * {@inheritdoc}
         */
        public function save($key, $value = null, int $lifetime = 0)
        {
                $command = new Save($this, $key, $value);
                $command->applyAll(function($keys) use($lifetime) {
                        foreach ($keys as $key => $val) {
                                $this->setEntry($key, $val);
                                $keys[$key] = true;
                        }
                        return $keys;
                });

                return $command->getResult();
        }

        /**
         * Compact cache.
         * 
         * Remove cache entries with expired timestamp. This method has 
         * no effect if configured lifetime is 0 and lifetime is not passed
         * as argument.
         * 
         * @param int $lifetime The lifetime to use.
         */
        public function compact(int $lifetime = 0)
        {
                if (($lifetime = $this->getLifetime($lifetime)) !== 0) {
                        $expire = time() - $lifetime;

                        foreach ($this->_cached as $key => $data) {
                                if ($data->stamp < $expire) {
                                        unset($this->_cached[$key]);
                                }
                        }
                }
        }

        /**
         * {@inheritdoc}
         */
        public function serialize(): string
        {
                $formatter = $this->getFormatter();
                return $formatter->onSave($this->_cached);
        }

        /**
         * {@inheritdoc}
         */
        public function unserialize($serialized): void
        {
                $formatter = $this->getFormatter();
                $this->_cached = $formatter->onRead($serialized);
        }

        /**
         * {@inheritdoc}
         */
        public function offsetExists($offset): bool
        {
                return isset($this->_cached[$offset]);
        }

        /**
         * {@inheritdoc}
         */
        public function offsetGet($offset)
        {
                return $this->_cached[$offset];
        }

        /**
         * {@inheritdoc}
         */
        public function offsetSet($offset, $value): void
        {
                $this->_cached[$offset] = new MemoryDataEntry($value, time());
        }

        /**
         * {@inheritdoc}
         */
        public function offsetUnset($offset): void
        {
                unset($this->_cached[$offset]);
        }

}
