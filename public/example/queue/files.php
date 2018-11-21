<?php

use Batchelor\Controller\Example\ExamplePage;

class FilesPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("List files", "files.inc");
        }

}
