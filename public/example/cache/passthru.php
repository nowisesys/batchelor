<?php

use Batchelor\Controller\Example\ExamplePage;

class PassthruPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Passthru (ignore) cache", "passthru.inc", function() {
                        include("support/footer.inc");
                });
        }

}
