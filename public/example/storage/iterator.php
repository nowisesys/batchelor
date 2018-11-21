<?php

use Batchelor\Controller\Example\ExamplePage;

class IteratorPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Recursive iterator", "iterator.inc");
        }

}
