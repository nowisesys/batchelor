<?php

use Batchelor\Controller\Example\ExamplePage;

class TreePage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("ASCII tree", "tree.inc");
        }

}
