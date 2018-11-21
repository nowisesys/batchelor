<?php

use Batchelor\Controller\Example\ExamplePage;

class JobsPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Get jobs", "jobs.inc");
        }

}
