<?php

use Batchelor\Controller\Example\ExamplePage;

class StackedPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Stacked cache", "stacked.inc", function() {
                        include("support/footer.inc");
                });
        }

}
