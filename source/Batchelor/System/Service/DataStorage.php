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

use Batchelor\Storage\Directory;
use Batchelor\Storage\File;
use Batchelor\System\Component;
use LogicException;

/**
 * The data storage service.
 * 
 * Calling this service will create the data directory if missing. The layout 
 * inside the data directory will be similar to:
 * 
 *    data                      (the root directory)
 *      ├── archive
 *      ├── backup -> /var/backups/chemgps
 *      ├── db
 *      │   ├── accepted
 *      │   ├── incoming
 *      │   └── rejected
 *      ├── jobs
 *      │   └── job-cleanup.sh -> /usr/local/chemgps/bin/chemgps-cleanup.sh
 *      ├── map
 *      │   ├── hostid
 *      │   └── inaddr
 *      ├── publish
 *      │   └── index.ser
 *      └── stat
 *          ├── all
 *          └── cache.ser
 *
 * Location and permissing is gathered using the application config service. Sub
 * directories should be created relative to the data directory:
 * 
 * <code>
 * $subdir = $datadir->useDirectory("db/accepted");
 * </code>
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class DataStorage extends Component
{

        /**
         * The root directory.
         * @var Directory 
         */
        public $root;

        /**
         * Constructor.
         * @param string $path The directory path.
         * @param int $mode The directory permissions.
         */
        public function __construct(string $path = null, int $mode = 0)
        {
                if (empty($path)) {
                        $path = $this->app->data->path;
                }
                if (empty($mode)) {
                        $mode = $this->app->data->mode;
                }
                if (isset($path) && isset($mode)) {
                        $this->setDirectory($path, $mode);
                } elseif (isset($path)) {
                        $this->setDirectory($path, 0750);
                } else {
                        throw new LogicException("The application data directory path is missing");
                }
        }

        /**
         * Set root directory.
         * @param string $path The path to data directory.
         * @param int $mode The optional directory permissions.
         */
        private function setDirectory(string $path, int $mode)
        {
                $this->root = $this->openDirectory(
                    new Directory(), $path, $mode
                );
        }

        /**
         * Get root directory.
         * @return Directory
         */
        public function getDirectory()
        {
                return $this->root;
        }

        /**
         * Use directory.
         * 
         * The directory will be created if missing. The permissions are inherited 
         * from the data directory if $mode is unused.
         * 
         * @param string $path The path to data directory.
         * @param int $mode The optional directory permissions.
         * @return Directory
         */
        public function useDirectory(string $path, int $mode = 0)
        {
                return $this->openDirectory($this->root, $path, $mode);
        }

        /**
         * Get permission from data directory.
         * @return int
         */
        public function getPermission()
        {
                return $this->root->getPathInfo()->getPerms();
        }

        /**
         * Open and return directory.
         * 
         * @param Directory $parent The parent directory.
         * @param string $path The path to data directory.
         * @param int $mode The optional directory permissions.
         * @return Directory
         */
        private function openDirectory(Directory $parent, string $path, int $mode = 0)
        {
                if (empty($mode)) {
                        $mode = $this->getPermission();
                }

                if ($parent->exists($path)) {
                        $directory = $parent->open($path);
                        return $directory;
                } else {
                        $directory = $parent->create($path, $mode);
                        return $directory;
                }
        }

        /**
         * Get file object.
         * 
         * The directory path is created if missing. Returns a file object
         * inside the directory. If filename is absolute, then the path is
         * not created.
         * 
         * The file is not actually created before some content is written
         * to the file object.
         * 
         * @param string $filename The filename path.
         * @return File
         */
        public function addFile(string $filename)
        {
                if ($filename[0] == '/') {
                        return new File($filename);
                }

                return $this->useDirectory(
                        dirname($filename)
                    )->getFile(
                        basename($filename)
                );
        }

}
