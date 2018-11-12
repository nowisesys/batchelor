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

use BadFunctionCallException;
use Batchelor\Cache\Backend;
use Batchelor\Cache\Backend\Base;
use Batchelor\Cache\Backend\Extension\ShmOp\Manager;
use Batchelor\Cache\Backend\Extension\ShmOp\Segment;
use Batchelor\Cache\Command\Delete;
use Batchelor\Cache\Command\Exists;
use Batchelor\Cache\Command\Read;
use Batchelor\Cache\Command\Save;

/**
 * Shared memory backend for UNIX.
 * 
 * This class implements a cache backend using shared memory (shmop)
 *
 * @author Anders Lövgren (Nowise Systems)
 * @see http://php.net/manual/en/book.shmop.php
 */
class ShmOp extends Base implements Backend
{

        /**
         * The handle manager.
         * @var Manager 
         */
        private $_manager;

        /**
         * Constructor.
         * @param array $options The cache options.
         */
        public function __construct($options = [])
        {
                if (!extension_loaded("shmop")) {
                        throw new BadFunctionCallException("The shmop extension is not loaded");
                }

                parent::__construct($options, [
                        'format'   => 'php',
                        'prefix'   => 'batchelor',
                        'lifetime' => 0,
                        'flag'     => Segment::OPEN_CREATE,
                        'mode'     => 0644,
                        'size'     => 400
                ]);

                $this->_manager = new Manager(parent::getOptions());
        }

        /**
         * Get shared memory segment.
         * 
         * @param string $key The segment name.
         * @param int $size The requested segment size.
         * @return Segment
         */
        private function getHandle(string $key, int $size = 0): Segment
        {
                return $this->_manager->fetch($key, $size);
        }

        /**
         * {@inheritdoc}
         */
        public function delete($key)
        {
                $command = new Delete($this, $key);
                $command->applyOne(function($key, $val) {
                        if (Segment::exist($key)) {
                                $handle = $this->getHandle($key);
                                return $handle->delete();
                        } else {
                                return true;    // We don't care if segment is missing or deleted.
                        }
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
                        return Segment::exist($key);
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
                        $handle = $this->getHandle($key);
                        $result = $handle->read();
                        return $formatter->onRead(trim($result));
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
                        $result = $formatter->onSave($val);
                        $handle = $this->getHandle($key, strlen($result));
                        return $handle->write($result);
                });

                return $command->getResult();
        }

}
