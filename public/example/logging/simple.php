<?php

use Batchelor\Web\ExamplePage;

class SimplePage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Simple", "simple.inc");
        }

}
