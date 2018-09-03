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

use BadFunctionCallException;
use Batchelor\Cache\Backend;
use Batchelor\Cache\Command\Delete;
use Batchelor\Cache\Command\Exists;
use Batchelor\Cache\Command\Read;
use Batchelor\Cache\Command\Save;

/**
 * The APCu cache backend.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class APCu extends Base implements Backend
{

        /**
         * Constructor.
         * @param array $options The cache options.
         */
        public function __construct($options = [])
        {
                if (!extension_loaded("apcu")) {
                        throw new BadFunctionCallException("Extension APCu is not loaded");
                }

                parent::__construct($options, [
                        'format'   => 'native',
                        'prefix'   => 'batchelor',
                        'lifetime' => 28800
                ]);
        }

        /**
         * {@inheritdoc}
         */
        public function delete($key)
        {
                $command = new Delete($this, $key);
                $command->applyAll(function($keys) {
                        // 
                        // The result array contains missing or failed keys.
                        // 
                        if (!($result = apcu_delete(array_keys($keys)))) {
                                return array_fill_keys(array_keys($keys), false);
                        }
                        foreach ($result as $key) {
                                $keys[$key] = apcu_exists($key) ? false : true;
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
                $command->applyAll(function($keys) {
                        $result = apcu_exists(array_keys($keys));
                        return array_merge($keys, $result);
                });

                return $command->getResult(is_string($key));
        }

        /**
         * {@inheritdoc}
         */
        public function read($key, int $lifetime = 0)
        {
                $formatter = $this->getFormatter();

                $command = new Read($this, $key);
                $command->applyAll(function($keys) use($formatter) {
                        if (!($result = apcu_fetch(array_keys($keys)))) {
                                return array_fill_keys(array_keys($keys), false);
                        }
                        foreach ($result as $key => $val) {
                                $result[$key] = $formatter->onRead($val);
                        }
                        return $result;
                });

                return $command->getResult();
        }

        /**
         * {@inheritdoc}
         */
        public function save($key, $value = null, int $lifetime = 0)
        {
                $formatter = $this->getFormatter();

                $command = new Save($this, $key, $value);
                $command->applyOne(function($key, $val) use($formatter, $lifetime) {
                        $content = $formatter->onSave($val);
                        return apcu_store(
                            $key, $content, $this->getLifetime($lifetime)
                        );
                });

                return $command->getResult();
        }

}
