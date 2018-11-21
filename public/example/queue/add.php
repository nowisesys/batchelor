<?php

use Batchelor\Controller\Example\ExamplePage;

class AddPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Add job", "add.inc");
        }

}
