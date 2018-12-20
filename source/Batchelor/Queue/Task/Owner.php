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

namespace Batchelor\Queue\Task;

use Batchelor\System\Security\User;
use Batchelor\System\Service\Config;
use Batchelor\System\Service\Security;
use Batchelor\System\Services;
use InvalidArgumentException;

/**
 * The job owner.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Owner
{

        /**
         * The remote address.
         * @var string
         */
        public $addr;
        /**
         * The remote hostname.
         * @var string 
         */
        public $host;
        /**
         * The authenticated user (if any).
         * @var string 
         */
        public $user;
        /**
         * The host ID.
         * @var string 
         */
        public $hostid;

        /**
         * Constructor.
         * @param string $hostid The host ID.
         */
        public function __construct(string $hostid)
        {
                $this->hostid = $hostid;
                $this->user = $this->getPrincipal();

                if (PHP_SAPI != 'cli') {
                        $this->addr = filter_input(INPUT_SERVER, "REMOTE_ADDR");
                        $this->host = gethostbyaddr($this->addr);
                } else {
                        $this->addr = "127.0.0.1";
                        $this->host = "localhost";
                }
        }

        /**
         * Get security service.
         * @return Security
         */
        private function getSecurity(): Security
        {
                return Services::getInstance()
                        ->getService("security");
        }

        /**
         * Get user object.
         * @return User
         */
        private function getUser(): User
        {
                return $this->getSecurity()->getUser();
        }

        /**
         * Get user principal name.
         * @return string
         */
        private function getPrincipal(): string
        {
                if (!($user = $this->getUser())) {
                        return "";
                } elseif ($user->isAuthenticated()) {
                        return $user->getPrincipal()->getPrincipalName();
                } else {
                        return "";
                }
        }

        /**
         * Check if job owner is trusted.
         * @return bool
         * @throws InvalidArgumentException
         */
        public function isTrusted(): bool
        {
                if (!($config = $this->getConfig())) {
                        return false;
                } elseif (!($trusted = $config->trusted)) {
                        return false;
                } elseif (!is_callable($config->trusted)) {
                        throw new InvalidArgumentException("The trusted is setting is defined, but not a callable");
                } else {
                        return $trusted($this);
                }
        }

        /**
         * Get application config.
         * @return Config
         */
        private function getConfig(): Config
        {
                return Services::getInstance()
                        ->getService("app");
        }

}
