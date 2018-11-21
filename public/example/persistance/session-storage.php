<?php

use Batchelor\Controller\Example\ExamplePage;

class SessionStoragePage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Session Storage", "session-storage.inc");
        }

}
