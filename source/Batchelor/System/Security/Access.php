<?php

/*
 * Copyright (C) 2018 Anders Lövgren (Nowise Systems).
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Batchelor\System\Security;

use InvalidArgumentException;
use Batchelor\System\Component;
use Batchelor\System\Security\Facade\Actions;
use Batchelor\System\Security\Facade\Roles;
use Batchelor\System\Service\Config;

/**
 * Access control class.
 * 
 * The access control list are on this form:
 * 
 * <code>
 * // 
 * // The value for action and users can be an array, string, 
 * // object (facade) or callable.
 * // 
 * $access = [
 *      'role1' => [
 *              'action' => ...
 *              'users'  => ...
 *      ],
 *      'role2' => ...  // Other roles follows the same pattern.
 * ]
 * </code>
 * 
 * Each role should define the action and users key with either an string or 
 * array. The special string '*' can be used to match anything.
 * 
 * It is also possible to use an callable or facade object for handling more
 * dynamic cases (see examples/system/security). Here's an code snippet that 
 * shows the idea:
 * 
 * 
 * Using a callable is a method to use when granting the role should be handled 
 * external or by invoking a function. The callable should accept two arguments
 * were the first is the role, the second is the username and return true if
 * username has requested role:
 * 
 * <code>
 * $access = [
 *      'admin' => 
 *              'users' => static function('role1', $user) {
 *                      if ($user == "anders") {
 *                              return true;
 *                      } else {
 *                              return false;
 *                      }
 *              },
 *              'action' => '*'         // Admins can do anything
 * </code>
 * 
 * Intended use of ACL:
 * 
 * 1. Use hasRole() in frontend controller for acess restriction to 
 *    protected pages.
 *  
 * 2. Call isGranted() in action controller before performing i.e. an
 *    database update.
 * 
 * @author Anders Lövgren (Nowise Systems)
 * 
 * @see Roles
 * @see Actions
 */
class Access extends Component
{

        /**
         * The access control list (ACL).
         * @var array 
         */
        private $_access;

        /**
         * Constructor.
         * @param array $access The access control list (ACL).
         */
        public function __construct(array $access = [])
        {
                if (empty($access)) {
                        $this->setAccess($this->getConfig());
                } else {
                        $this->setAccess($access);
                }
        }

        /**
         * Get access control list (ACL).
         * @return array
         */
        public function getAccessList(): array
        {
                return $this->_access;
        }

        /**
         * Set access control list (ACL).
         * @param array $access The access control list (ACL).
         */
        private function setAccess(array $access)
        {
                $this->_access = $access;
        }

        /**
         * Get all role names.
         * @return array
         */
        public function getRoles()
        {
                return array_keys($this->_access);
        }

        /**
         * Check if role can be enumerated.
         * 
         * Return true if role can be finitly enumerated. That is, if all roles
         * can be returned as an array.
         * 
         * @param string $role The role name.
         * @return bool
         */
        public function isEnumeratable(string $role): bool
        {
                $list = $this->_access[$role];

                if ($list instanceof Roles) {
                        return $list->canEnumerate();
                }
                if (is_string($list) || is_array($list)) {
                        return true;
                }

                return false;
        }

        /**
         * Get all users having role.
         * 
         * @param string $role The role name.
         * @return array 
         */
        public function getUsers(string $role): array
        {
                if (!isset($this->_access[$role]['users'])) {
                        throw new InvalidArgumentException("The role $role has no users list");
                }

                $list = $this->_access[$role]['users'];

                if ($list instanceof Roles) {
                        return $list->getUsers($role);
                }
                if (is_string($list)) {
                        return [$list];
                }
                if (is_array($list)) {
                        return $list;
                }
                if (is_callable($list)) {
                        return [];
                }
        }

        /**
         * Check if user has role.
         * 
         * @param string $role The role name.
         * @param string $user The username.
         * @return bool
         */
        public function hasRole(string $role, string $user): bool
        {
                if (!isset($this->_access[$role])) {
                        throw new InvalidArgumentException("The role $role don't exists");
                }

                $list = $this->_access[$role]['users'];

                if ($list instanceof Roles) {
                        return $list->hasRole($role, $user);
                }
                if (is_string($list) && $list == '*') {
                        return true;
                }
                if (is_string($list) && $list == $user) {
                        return true;
                }
                if (is_array($list) && in_array($user, $list)) {
                        return true;
                }
                if (is_callable($list)) {
                        return $list($role, $user);
                }

                return false;
        }

        /**
         * Check if action is granted.
         * 
         * @param string $action The requested action.
         * @param string $user The username,
         * @return bool 
         */
        public function isGranted(string $action, string $user): bool
        {
                foreach ($this->getRoles() as $role) {
                        if (!$this->hasRole($role, $user)) {
                                continue;
                        }
                        if ($this->hasAction($action, $role, $user)) {
                                return true;
                        }
                }

                return false;
        }

        /**
         * Check if user has action in role.
         * 
         * @param string $action The requested action.
         * @param string $role The role name.
         * @param string $user The username.
         * @return bool 
         * @throws InvalidArgumentException
         */
        private function hasAction(string $action, string $role, string $user): bool
        {
                if (!isset($this->_access[$role]['action'])) {
                        throw new InvalidArgumentException("The action for role $role don't exists");
                }

                $list = $this->_access[$role]['action'];

                if ($list instanceof Actions) {
                        return $list->hasAction($action, $user);
                }
                if (is_string($list) && $list == '*') {
                        return true;
                }
                if (is_string($list) && $list == $action) {
                        return true;
                }
                if (is_array($list) && in_array($action, $list)) {
                        return true;
                }
                if (is_callable($list)) {
                        return $list($action, $user);
                }

                return false;
        }

        /**
         * Get application config.
         * @return array 
         */
        private function getConfig(): array
        {
                if (($config = $this->getService("app"))) {
                        if (!isset($config->access)) {
                                return [];
                        } elseif (is_object($config->access)) {
                                return Config::toArray($config->access);
                        } else {
                                return $config->access;
                        }
                }
        }

}
