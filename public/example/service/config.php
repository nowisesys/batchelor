<?php

use Batchelor\Controller\Example\ExamplePage;

class ConfigPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Config Service", "config.inc");
        }

}
