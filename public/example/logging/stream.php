<?php

use Batchelor\Controller\Example\ExamplePage;

class StreamPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Stream", "stream.inc");
        }

}
