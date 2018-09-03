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
 * The XCache backend.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class XCache extends Base implements \Batchelor\Cache\Backend
{

        /**
         * Constructor.
         * @param array $options The cache options.
         */
        public function __construct($options = [])
        {
                if (!extension_loaded("xcache")) {
                        throw new BadFunctionCallException("The XCache extension is not loaded");
                }

                parent::__construct($options, [
                        'format'   => 'native',
                        'prefix'   => 'batchelor',
                        'lifetime' => 28800
                ]);
        }
        
        /**
         * Increment counter.
         * 
         * @param string $key The counter name.
         * @param int $value The increment value.
         * @param int $lifetime The cache entry lifetime.
         * @return int
         */
        public function increment(string $key, int $value = 1, int $lifetime = 0): int
        {
                $lifetime = $this->getLifetime($lifetime);
                return xcache_inc($key, $value, $lifetime);
        }

        /**
         * Decrement counter.
         * 
         * @param string $key The counter name.
         * @param int $value The increment value.
         * @param int $lifetime The cache entry lifetime.
         * @return int
         */
        public function decrement(string $key, int $value = 1, int $lifetime = 0): int
        {
                $lifetime = $this->getLifetime($lifetime);
                return xcache_dec($key, $value, $lifetime);
        }

        /**
         * {@inheritdoc}
         */
        public function delete($key)
        {
                $command = new Delete($this, $key);
                $command->applyOne(function($key, $val) {
                        return xcache_unset($key);
                });

                return $command->getResult(is_string($key));
        }

        /**
         * {@inheritdoc}
         */
        public function exists($key)
        {
                $command = new Exists($this, $key);
                $command->applyOne(function($key, $val) {
                        return xcache_isset($key);
                });

                return $command->getResult(is_string($key));
        }

        /**
         * {@inheritdoc}
         */
        public function read($key)
        {
                $formatter = $this->getFormatter();

                $command = new Read($this, $key);
                $command->applyOne(function($key, $val) use($formatter) {
                        $content = xcache_get($key);
                        $content = $formatter->onRead($content);
                        return $content;
                });

                return $command->getResult();
        }

        /**
         * {@inheritdoc}
         */
        public function save($key, $value = null, int $lifetime = 0)
        {
                $formatter = $this->getFormatter();
                $lifetime = $this->getLifetime($lifetime);

                $command = new Save($this, $key, $value);
                $command->applyOne(function($key, $val) use($formatter, $lifetime) {
                        $content = $formatter->onSave($val);
                        return xcache_set($key, $content, $lifetime);
                });

                return $command->getResult();
        }

}
