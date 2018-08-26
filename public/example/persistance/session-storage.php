<?php

use Batchelor\Web\ExamplePage;

class SessionStoragePage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Cookie Storage", "session-storage.inc");
        }

}
