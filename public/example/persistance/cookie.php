<?php

use Batchelor\Controller\Example\ExamplePage;

class CookiePage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Cookie Class", "cookie.inc");
        }

}
