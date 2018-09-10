<?php

use Batchelor\Web\ExamplePage;

class StackedPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Stacked cache", "stacked.inc", function() {
                        include("support/footer.inc");
                });
        }

}
