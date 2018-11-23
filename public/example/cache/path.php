<?php

use Batchelor\Controller\Example\ExamplePage;

class PathPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Path cache", "path.inc", function() {
                        include("support/footer.inc");
                });
        }

}
