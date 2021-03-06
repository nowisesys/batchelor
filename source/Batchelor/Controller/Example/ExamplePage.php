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

use Batchelor\System\Timer;
use UUP\Site\Page\Web\StandardPage;

/**
 * The example page.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class ExamplePage extends StandardPage
{

        /**
         * The example file.
         * @var string 
         */
        private $_filename;
        /**
         * The content template.
         * @var string 
         */
        private $_template;
        /**
         * Callback render function.
         * @var callable 
         */
        private $_callback;

        /**
         * Constructor.
         * @param string $title The page title.
         * @param string $filename The example file.
         */
        public function __construct($title, $filename, $callback = false)
        {
                parent::__construct($title);

                if ($this->config->auth) {
                        $this->authorize();
                }

                $this->_filename = $filename;
                $this->_template = sprintf("%s/content/example.inc", $this->config->template);
                $this->_callback = $callback;
        }

        public function printContent()
        {
                $filename = $this->_filename;
                $callback = $this->_callback;

                $timer = new Timer();
                $timer->start();

                require($this->_template);

                $timer->stop();
                printf("Example finished in %f sec\n", $timer->elapsed());
        }

}
