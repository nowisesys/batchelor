<?php

use Batchelor\Web\ExamplePage;

class ServicePage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Logger service", "service.inc");
        }

}
