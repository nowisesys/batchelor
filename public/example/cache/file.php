<?php

use Batchelor\Controller\Example\ExamplePage;

class FilePage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("File cache", "file.inc", function() {
                        include("support/footer.inc");
                });
        }

}
