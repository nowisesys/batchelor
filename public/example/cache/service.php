<?php

use Batchelor\Web\ExamplePage;

class ServicePage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Cache service", "service.inc", function() {
                        include("support/footer.inc");
                });
        }

}
