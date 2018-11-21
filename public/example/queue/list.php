<?php

use Batchelor\Controller\Example\ExamplePage;

class ListPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("List job", "list.inc");
        }

}
