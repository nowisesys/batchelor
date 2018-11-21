<?php

use Batchelor\Controller\Example\ExamplePage;

class RequestPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Request", "request.inc");
        }

}
