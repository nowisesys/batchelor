<?php

use Batchelor\Web\ExamplePage;

class DetectPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Detect cache", "detect.inc", function() {
                        include("support/footer.inc");
                });
        }

}
