<?php

use Batchelor\Controller\Example\ExamplePage;

class MonitorPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Monitor", "monitor.inc");
        }

}
