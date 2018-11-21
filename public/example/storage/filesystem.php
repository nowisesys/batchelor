<?php

use Batchelor\Controller\Example\ExamplePage;

class FilesystemPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Filesystem class", "filesystem.inc");
        }

}
