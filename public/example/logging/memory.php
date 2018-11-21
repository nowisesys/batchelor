<?php

use Batchelor\Controller\Example\ExamplePage;

class MemoryPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Memory", "memory.inc");
        }

}
