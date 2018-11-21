<?php

use Batchelor\Controller\Example\ExamplePage;

class SyslogPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Syslog", "syslog.inc");
        }

}
