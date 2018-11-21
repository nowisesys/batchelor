<?php

use Batchelor\Controller\Example\ExamplePage;

class ServicePage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Logger service", "service.inc");
        }

}
