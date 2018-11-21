<?php

use Batchelor\Controller\Example\ExamplePage;

class MemoryPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("RAM memory cache", "memory.inc", function() {
                        include("support/footer.inc");
                });
        }

}
