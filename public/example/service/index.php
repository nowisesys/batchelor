<?php

use Batchelor\Controller\Example\ExampleIndex;

class IndexPage extends ExampleIndex
{

        public function __construct()
        {
                parent::__construct("Services");
        }

        public function printContent()
        {
                include("index.inc");
                parent::printContent();
        }
}
