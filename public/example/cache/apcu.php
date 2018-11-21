<?php

use Batchelor\Controller\Example\ExamplePage;

class ApcuPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("APCu cache", "apcu.inc", function() {
                        include("support/footer.inc");
                });
        }

}
