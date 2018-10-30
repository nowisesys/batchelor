<?php

use Batchelor\Web\ExamplePage;

class StatusPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Show status", "status.inc");
        }

}
