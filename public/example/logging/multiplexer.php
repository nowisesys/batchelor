<?php

use Batchelor\Controller\Example\ExamplePage;

class MultiplexerPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Multiplexer", "multiplexer.inc");
        }

}
