<?php

use Batchelor\Controller\Example\ExamplePage;

class DirectoryPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Directory class", "directory.inc");
        }

}
