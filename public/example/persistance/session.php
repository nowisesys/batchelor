<?php

use Batchelor\Controller\Example\ExamplePage;

class SessionPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Session Class", "session.inc");
        }

}
