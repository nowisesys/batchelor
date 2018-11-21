<?php

use Batchelor\Controller\Example\ExamplePage;

class ScannerPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Directory scanner", "scanner.inc");
        }

}
