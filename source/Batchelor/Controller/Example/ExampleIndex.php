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

namespace Batchelor\Controller\Example;

use UUP\Site\Page\Web\StandardPage;

/**
 * The example index.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class ExampleIndex extends StandardPage
{

        public function __construct(string $title)
        {
                parent::__construct($title);

                if ($this->config->auth) {
                        $this->authorize();
                }
        }

        public function printContent()
        {
                $data = include('standard.menu');

                foreach ($data['data'] as $text => $link) {
                        printf("<a class=\"w3-btn w3-hide-large\" href=\"%s\" style=\"min-width: 150px; margin-top: 5px\">%s</a>\n", $link, $text);
                }
        }

}
