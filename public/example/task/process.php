<?php

use Batchelor\Controller\Example\ExamplePage;

class ProcessPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Process", "process.inc");
        }

}
