<?php

use Batchelor\Web\ExamplePage;

class FilePage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("File cache", "file.inc", function() {
                        include("support/footer.inc");
                });
        }

}