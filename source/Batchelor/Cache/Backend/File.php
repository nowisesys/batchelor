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

use Batchelor\Cache\Backend;
use Batchelor\Cache\Command\Delete;
use Batchelor\Cache\Command\Exists;
use Batchelor\Cache\Command\Read;
use Batchelor\Cache\Command\Save;
use Batchelor\Storage\Directory;
use Batchelor\Storage\File as StorageFile;
use RuntimeException;

/**
 * The files cache.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class File extends Base implements Backend
{

        /**
         * The cache directory.
         * @var Directory 
         */
        private $_cache;

        /**
         * Constructor.
         * @param array $options The cache options.
         */
        public function __construct($options = [])
        {
                parent::__construct($options, [
                        'path'     => 'cache', // Absolute path or relative to data directory.
                        'mode'     => 0750,
                        'format'   => 'php',
                        'prefix'   => 'batchelor',
                        'suffix'   => '.ser',
                        'lifetime' => 604800
                ]);
        }

        /**
         * Get cache directory.
         * @return Directory The cache directory.
         */
        private function getDirectory()
        {
                if (isset($this->_cache)) {
                        return $this->_cache;
                }

                if (!($path = $this->getOption('path'))) {
                        $path = "cache";
                }
                if (!($mode = $this->getOption('mode'))) {
                        $mode = 0750;
                }

                if (!($data = $this->data->root)) {
                        throw new RuntimeException("The data directory service is missing");
                }

                if (!$data->exists($path)) {
                        $cache = $data->create($path, $mode);
                        return $this->_cache = $cache;
                } else {
                        $cache = $data->open($path);
                        return $this->_cache = $cache;
                }
        }

        /**
         * Check if file has expired.
         * 
         * @param StorageFile $file The file object.
         * @param int $lifetime The file lifetime.
         * @return bool 
         */
        private function hasExpired($file, $lifetime = 0)
        {
                $lifetime = $this->getLifetime($lifetime);
                $modified = $file->getMTime();
                $expiring = time() - $lifetime;

                return $lifetime == 0 ? false : $modified < $expiring;
        }

        /**
         * Get cache file.
         * 
         * @param string $name The filename.
         * @param int $lifetime The file lifetime.
         * @param string $mode The command mode.
         * @return StorageFile 
         */
        private function getFile($name, $lifetime = 0, $mode = false)
        {
                $cache = $this->getDirectory();

                if (strstr($name, "/")) {
                        $cache->create($cache->getFile($name)->getDirname());
                }

                if (!($file = $cache->getFile($name))) {
                        return false;
                } elseif ($mode == 'save') {
                        return $file;
                } elseif (!$file->isFile()) {
                        return false;
                } elseif ($mode == 'delete') {
                        return $file;
                } elseif ($this->hasExpired($file, $lifetime)) {
                        $file->delete();
                        return false;
                } else {
                        return $file;
                }
        }

        /**
         * {@inheritdoc}
         */
        public function delete($key)
        {
                $command = new Delete($this, $key);
                $command->applyOne(function($key, $val) {
                        if (!($file = $this->getFile($key, 0, 'delete'))) {
                                return true;
                        } else {
                                $file->delete();
                                return $file->isFile() == false;
                        }
                });

                return $command->getResult(is_string($key));
        }

        /**
         * {@inheritdoc}
         */
        public function exists($key)
        {
                $lifetime = $this->getLifetime();

                $command = new Exists($this, $key);
                $command->applyOne(function($key, $val) use($lifetime) {
                        if (($this->getFile($key, $lifetime))) {
                                return true;
                        }
                });

                return $command->getResult(is_string($key));
        }

        /**
         * {@inheritdoc}
         */
        public function read($key)
        {
                $formatter = $this->getFormatter();
                $lifetime = $this->getLifetime();

                $command = new Read($this, $key);
                $command->applyOne(function($key, $val) use($formatter, $lifetime) {
                        if (($file = $this->getFile($key, $lifetime))) {
                                $content = $file->getContent();
                                $content = $formatter->onRead($content);
                                return $content;
                        }
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
                        if (($file = $this->getFile($key, $lifetime, 'save'))) {
                                $content = $formatter->onSave($val);
                                $content = $file->putContent($content);
                                return $content;
                        }
                });

                return $command->getResult();
        }

        /**
         * {@inheritdoc}
         */
        public function getCacheKey(string $key): string
        {
                return sprintf("%s%s", $this->getPrefixed($key), $this->getOption('suffix', '.ser'));
        }

        /**
         * Get prefixed cache key.
         * 
         * @param string $key The key name.
         * @return string 
         */
        private function getPrefixed(string $key): string
        {
                return trim(parent::getCacheKey($key), "-");
        }

}
