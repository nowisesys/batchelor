<?php

use Batchelor\Controller\Example\ExamplePage;

class NavigatePage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Navigate directory", "navigate.inc");
        }

}
