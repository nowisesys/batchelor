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

use Batchelor\Cache\Frontend;

/**
 * The queue name mapper.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class QueueMapper extends Frontend
{

        /**
         * Constructor.
         * @param array $options The mapper options.
         */
        public function __construct(array $options = [])
        {
                if (!isset($options['options'])) {
                        $options['options'] = [];
                }
                if ($options['options']['lifetime'] != 0) {
                        $options['options']['lifetime'] = 0;
                }
                if ($options['type'] == 'file') {
                        $options['options']['path'] = 'cache/mapper';
                }

                parent::__construct($options['type'], $options['options']);
        }

}
