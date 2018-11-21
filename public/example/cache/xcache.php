<?php

use Batchelor\Controller\Example\ExamplePage;

class XCachePage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("XCache", "xcache.inc", function() {
                        include("support/footer.inc");
                });
        }

}
