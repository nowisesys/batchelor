<?php

use Batchelor\Controller\Example\ExamplePage;

class CapturePage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Capture", "capture.inc");
        }

}
