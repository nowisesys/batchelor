<?php

use Batchelor\Web\ExamplePage;

class MemoryPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("RAM memory cache", "memory.inc", function() {
                        include("support/footer.inc");
                });
        }

}
