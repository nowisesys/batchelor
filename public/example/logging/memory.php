<?php

use Batchelor\Web\ExamplePage;

class MemoryPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Memory", "memory.inc");
        }

}
