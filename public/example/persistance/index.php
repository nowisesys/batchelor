<?php

use Batchelor\Web\ExamplePageIndex;

class IndexPage extends ExamplePageIndex
{

        public function __construct()
        {
                parent::__construct("Persistance");
        }

        public function printContent()
        {
                include("index.inc");
                parent::printContent();
        }
}