<?php

use Batchelor\Controller\Example\ExamplePage;

class RemovePage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Remove job", "remove.inc");
        }

}
