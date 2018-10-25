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

namespace Batchelor\Storage;

/**
 * The locate file class.
 * 
 * Use this class to locate a file in standard locations, include path, by
 * envirinment variable and optional locations.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Locate
{

        /**
         * The locations to search.
         * @var array 
         */
        private $_locations;

        /**
         * Constructor.
         * @param array $custom Custom search locations.
         */
        public function __construct(array $custom = [])
        {
                $locations = [];

                if (defined('BATCHELOR_APP_ROOT')) {
                        $locations[] = BATCHELOR_APP_ROOT;
                        $locations[] = sprintf("%s/config", BATCHELOR_APP_ROOT);
                }
                if (($root = getenv("BATCHELOR_APP_ROOT"))) {
                        $locations[] = $root;
                        $locations[] = sprintf("%s/config", $root);
                }
                if (!empty($custom)) {
                        $locations = array_merge($locations, $custom);
                }

                $this->_locations = $locations;
        }

        /**
         * Add locations to search.
         * @param array $locations The directory locations.
         */
        public function addLocations(array $locations)
        {
                $this->_locations = array_merge($this->_locations, $locations);
        }

        /**
         * Add location to search.
         * @param string $location The directory location.
         */
        public function addLocation(string $location)
        {
                $this->_locations[] = $location;
        }

        /**
         * Set locations to search.
         * @param array $locations The directory locations.
         */
        public function setLocations(array $locations)
        {
                $this->_locations = $locations;
        }

        /**
         * Use PHP include path.
         */
        public function useIncludePath()
        {
                $this->_locations = array_merge(
                    explode(":", get_include_path()), $this->_locations
                );
        }

        /**
         * Include relative pathes.
         */
        public function useRelativePath()
        {
                $this->_locations[] = __DIR__ . '/../../..';
        }

        /**
         * Locate filename in search locations.
         * 
         * <code>
         * $config = $locate->getFilepath('config/services.inc");
         * </code>
         * 
         * Returns the located filename or false if missing. The returned 
         * filename might have a relative path.
         * 
         * @param string $filename The filename.
         * @return string|bool
         */
        public function getFilepath(string $filename)
        {
                foreach ($this->_locations as $location) {
                        $pathname = sprintf("%s/%s", $location, $filename);

                        if (file_exists($pathname)) {
                                return $pathname;
                        }
                }

                return false;
        }

}
