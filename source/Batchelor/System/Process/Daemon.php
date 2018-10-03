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

namespace Batchelor\System\Process;

use Batchelor\Storage\FileSystem;
use InvalidArgumentException;
use RuntimeException;

/**
 * The daemon class,
 * 
 * System process classes can derive from this class to get access to common
 * daemon functionality as creating pidfile, set runtime user or fork process 
 * into background (UNIX daemon).
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Daemon
{

        /**
         * The process daemoinizer.
         * @var Daemonizer 
         */
        private $_daemonizer;

        /**
         * Constructor.
         */
        public function __construct()
        {
                $this->_daemonizer = new Daemonizer();
        }

        /**
         * Get process daemonizer.
         * @return Daemonizer The daemonizer object.
         */
        public function getDaemonizer(): Daemonizer
        {
                return $this->_daemonizer;
        }

        /**
         * Set process detached from controlling terminal.
         */
        public function setDetached()
        {
                $this->_daemonizer->perform();
        }

        /**
         * Set process user.
         * 
         * @param int|string $user The username or UID.
         * @param bool $effective Set effective user.
         * @throws RuntimeException
         */
        public function setProcessUser($user, bool $effective = false)
        {
                if (is_string($user)) {
                        $uid = $this->getUserID($user);
                } elseif (is_int($user)) {
                        $uid = $user;
                } else {
                        throw new InvalidArgumentException("Expected an string or int as user argument");
                }

                if ($effective && !posix_seteuid($uid)) {
                        throw new RuntimeException("Failed set effective user $uid");
                } elseif (!posix_setuid($uid)) {
                        throw new RuntimeException("Failed set user $uid");
                }
        }

        /**
         * Set process group.
         * 
         * @param int|string $group The groupname or GID.
         * @param bool $effective Set effective group.
         * @throws RuntimeException
         */
        public function setProcessGroup($group, bool $effective = false)
        {
                if (is_string($group)) {
                        $gid = $this->getGroupID($group);
                } elseif (is_int($group)) {
                        $gid = $group;
                } else {
                        throw new InvalidArgumentException("Expected an string or int as group argument");
                }

                if ($effective && !posix_seteuid($gid)) {
                        throw new RuntimeException("Failed set effective group $gid");
                } elseif (!posix_setuid($gid)) {
                        throw new RuntimeException("Failed set group $gid");
                }
        }

        /**
         * Get UID from string.
         * 
         * @param string $user The username.
         * @return int
         * @throws RuntimeException
         */
        private function getUserID(string $user): int
        {
                if (!($info = posix_getpwnam($user))) {
                        throw new RuntimeException("Failed get user info");
                } else {
                        return $info['uid'];
                }
        }

        /**
         * Get GID from string.
         * @param string $group The group name.
         * @return int
         * @throws RuntimeException
         */
        private function getGroupID(string $group): int
        {
                if (!($info = posix_getgrnam($group))) {
                        throw new RuntimeException("Failed get group info");
                } else {
                        return $info['gid'];
                }
        }

        /**
         * Write PID to file.
         * 
         * @param string $path The file path.
         */
        public function setProcessFile(string $path)
        {
                if (!(new FileSystem)
                        ->getFile($path)
                        ->putContent(getmypid())) {
                        throw new RuntimeException("Failed write PID file");
                }
        }

}
