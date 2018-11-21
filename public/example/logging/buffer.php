<?php

use Batchelor\Controller\Example\ExamplePage;

class BufferPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Buffer", "buffer.inc");
        }

}
