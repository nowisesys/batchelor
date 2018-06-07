<?php

use UUP\Site\Page\Context\Menu\MenuData;
use UUP\Site\Page\Web\Component\Link;
use UUP\Site\Page\Web\Component\Links;
use UUP\Site\Page\Web\StandardPage;

/*
 * Copyright (C) 2015-2017 Anders Lövgren (Nowise Systems/BMC-IT, Uppsala University).
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

/**
 * Output HTML elements.
 * 
 * This is a helper class for the default theme. It's sort of a demonstration of
 * how to escape from the pain of mixing PHP code inline in HTML, but also shows
 * that we can take advantage of the IDE auto complete.
 */
class format
{

        /**
         * @var StandardPage
         */
        private $page;
        private $hash;

        /**
         * Constructor.
         * @param StandardPage $page The page to render.
         */
        public function __construct($page)
        {
                $this->page = $page;
                $this->hash = md5(basename(filter_input(INPUT_SERVER, 'REQUEST_URI')));
        }

        public function hash()
        {
                return $this->hash;
        }

        public function topmenu($small)
        {
                if ($this->page->template != "welcome") {
                        foreach ($this->page->topmenu as $name => $attr) {
                                $link = new Link($name, $attr);
                                $link->attr['class'] = ($small) ? 'w3-bar-item w3-mobile w3-button' : 'w3-bar-item w3-hover-white w3-hide-small';
                                $link->render($this->page->config->location);
                        }
                }
        }

        public function navmenu()
        {
                foreach ($this->page->navmenu as $menu) {
                        $links = new Links($menu);
                        $links->render($this->page->config->location);
                }
        }

        public function sidebar()
        {
                foreach ($this->page->sidebar as $sect => $data) {
                        $menu = new MenuData(array(
                                'head' => $sect,
                                'data' => $data
                        ));
                        $links = new Links($menu);
                        $links->render($this->page->config->location);
                }
        }

        public function sections()
        {
                printf("<div class=\"w3-row\">\n");
                foreach ($this->page->sections as $head => $cont) {
                        printf("<div class=\"w3-third w3-center w3-margin-16\">\n");
                        printf("<h1>%s</h1>\n", $head);
                        printf("<img src=\"%s\" class=\"w3-image\" style=\"max-height:230px;min-height:200px\">\n", $cont['image']);
                        printf("<p class=\"w3-text-grey\">%s</p>\n", $cont['text']);
                        printf("<button class=\"w3-btn w3-green\" onclick=\"location.href = '%s'\">More</button>\n", $cont['link']);
                        printf("</div>\n");
                }
                printf("</div>\n");
        }

        public function content()
        {
                $this->page->printContent();
        }

        public function headers()
        {
                $this->page->printHeader();
        }

        public function footer()
        {
                $publisher = $this->page->publisher;

                if ($publisher->count()) {
                        if ($publisher->updated) {
                                printf("<div class=\"updated\"><small>Modified: %s</small></div>\n", strftime("%x %X", $publisher->updated));
                        }
                        if ($publisher->copying) {
                                printf("<div class=\"copying\"><small>&copy; %s</small></div>\n", $publisher->copying);
                        }
                }
        }

        public function title()
        {
                return sprintf("%s - %s", $this->page->config->name, $this->page->title);
        }

}
