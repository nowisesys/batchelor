<?php

use Batchelor\Controller\Example\ExamplePage;

class HostidPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Host ID Service", "hostid.inc");
        }

}
