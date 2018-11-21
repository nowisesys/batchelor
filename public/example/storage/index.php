<?php

use Batchelor\Controller\Example\ExampleIndex;

class IndexPage extends ExampleIndex
{

        public function __construct()
        {
                parent::__construct("Directory");
        }

        public function printContent()
        {
                include("index.inc");
                parent::printContent();
        }
}
