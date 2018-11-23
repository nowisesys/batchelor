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

namespace Batchelor\Queue\Locator;

use Batchelor\Cache\Config;
use Batchelor\Cache\Frontend;

/**
 * The queue server mapper.
 * 
 * This class handles the hostid -> server config mapping. In the simpliest case 
 * there's only one queue (the local). 
 * 
 * In essential, this class answers the single question: Should this client be 
 * using the local queue or one of the remote queues?
 * 
 * The answer (from cache if exist) is a string containing either "local" or the
 * name of the remote queue config (i.e. "remote3").
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class QueueMapper extends Frontend
{

        /**
         * Constructor.
         */
        public function __construct()
        {
                $options = $this->getConfig();
                parent::__construct($options['type'], $options['options']);
        }

        /**
         * Get cache config.
         * @return array
         */
        private function getConfig(): array
        {
                return (new Config('mapper', 'persist'))->getOptions();
        }

}
