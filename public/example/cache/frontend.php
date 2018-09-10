<?php

use Batchelor\Web\ExamplePage;

class FrontendPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Frontend cache", "frontend.inc", function() {
                        include("support/footer.inc");
                });
        }

}
