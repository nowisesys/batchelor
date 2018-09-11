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

use Batchelor\Cache\Backend\APCu;
use Batchelor\Cache\Backend\Extension\Detected;
use Batchelor\Cache\Backend\Extension\ShmOp;
use Batchelor\Cache\Backend\Extension\Stacked;
use Batchelor\Cache\Backend\File;
use Batchelor\Cache\Backend\Memcached;
use Batchelor\Cache\Backend\Memory;
use Batchelor\Cache\Backend\Redis;
use Batchelor\Cache\Backend\XCache;
use Batchelor\Cache\Formatter\JsonSerialize;
use Batchelor\Cache\Formatter\NativeFormat;
use Batchelor\Cache\Formatter\PhpSerialize;
use LogicException;

/**
 * Creates cache backend.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Factory
{

        /**
         * Get first available cache backend.
         * 
         * Probe for available backends and use first detected. The list of probed
         * backend are sorted by performance, so this should give you the optimal
         * backend of those that are available.
         * 
         * The options are passed to the backend constructor and should have 
         * generic options (if at all). Use the exclude list to filter detected 
         * backends, i.e. to exclude the file backend.
         * 
         * @param array $options The backend options.
         * @param array $exclude The backends to exclude.
         * @return Backend The cache backend.
         */
        public static function getFirst(array $options = [], array $exclude = []): Backend
        {
                $detected = self::getAvailable($exclude);
                $instance = self::getBackend($detected[0], $options);

                return $instance;
        }

        /**
         * Get all available backends (except memory).
         * 
         * Probe for available backends and append each one to the result. The list 
         * can be optional filtered using the exclude array. The options array should
         * typical contain generic options as lifetime.
         * 
         * The returned list will contains names of all detected cache backend 
         * that can be used when creating the backend object:
         * 
         * <code>
         * $detected = Factory::getAvailable();
         * $backend  = Factory::getBackend($detected[0], $options);
         * </code>
         * 
         * @param array $exclude The backends to exclude.
         * @return array The array of backend names.
         * 
         * @see Factory::getBackends()
         */
        public static function getAvailable(array $exclude = []): array
        {
                $backends = [];

                if (extension_loaded("apcu") && !in_array("apcu", $exclude)) {
                        $backends[] = "apcu";
                }
                if (extension_loaded("xcache") && !in_array("xcache", $exclude)) {
                        $backends[] = "xcache";
                }
                if (extension_loaded("redis") && !in_array("redis", $exclude)) {
                        $backends[] = "redis";
                }
                if (extension_loaded("memcached") && !in_array("memcached", $exclude)) {
                        $backends[] = "memcached";
                }
                if (extension_loaded("shmop") && !in_array("shmop", $exclude)) {
                        $backends[] = "shmop";
                }

                if (!in_array("file", $exclude)) {
                        $backends[] = "file";
                }

                return $backends;
        }

        /**
         * Get all available backends (except memory).
         * 
         * Probe for available backends and append each one to the result. The list 
         * can be optional filtered using the exclude array. The options array should
         * typical contain generic options as lifetime.
         * 
         * This function will create instances of each detected cache backend. If 
         * this is not wanted, use the getAvailable() function instead that does the
         * same detection, but returns an array of string.
         * 
         * @param array $options The backend options.
         * @param array $exclude The backends to exclude.
         * @return array The array of backend objects.
         * 
         * @see Factory::getAvailable()
         */
        public static function getBackends(array $options = [], array $exclude = []): array
        {
                $backends = [];
                $detected = self::getAvailable($exclude);

                if (in_array("apcu", $detected)) {
                        $backends[] = new APCu($options);
                }
                if (in_array("xcache", $detected)) {
                        $backends[] = new XCache($options);
                }
                if (in_array("redis", $detected)) {
                        $backends[] = new Redis($options);
                }
                if (in_array("memcached", $detected)) {
                        $backends[] = new Memcached($options);
                }
                if (in_array("shmop", $detected)) {
                        $backends[] = new ShmOp($options);
                }
                if (in_array("file", $detected)) {
                        $backends[] = new File($options);
                }

                return $backends;
        }

        /**
         * Get cache backend.
         * 
         * @param string $type The backend type.
         * @param array $options The backend options.
         * @return Backend
         */
        public static function getBackend(string $type, array $options = [])
        {
                switch ($type) {
                        case 'apcu':
                                return new APCu($options);
                        case 'file':
                                return new File($options);
                        case 'memory':
                                return new Memory($options);
                        case 'memcached':
                                return new Memcached($options);
                        case 'shmop':
                                return new ShmOp($options);
                        case 'redis':
                                return new Redis($options);
                        case 'xcache':
                                return new XCache($options);
                        case 'stacked':
                                return new Stacked($options);
                        case 'detect':
                                return new Detected($options);
                        default:
                                throw new LogicException("Unknown cache backend $type");
                }
        }

        /**
         * Get read/save formatter.
         * @param string $type The format name.
         * @return Formatter
         */
        public static function getFormatter(string $type)
        {
                switch ($type) {
                        case 'native':
                                return new NativeFormat();
                        case 'php':
                                return new PhpSerialize();
                        case 'json':
                                return new JsonSerialize();
                        default:
                                throw new LogicException("Unknown read/save format type $type");
                }
        }

}
