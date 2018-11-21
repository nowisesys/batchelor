<?php

use Batchelor\Controller\Example\ExamplePage;

class InsertPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Insert jobs", "insert.inc");
        }

}
