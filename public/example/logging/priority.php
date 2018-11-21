<?php

use Batchelor\Controller\Example\ExamplePage;

class PriorityPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Priority", "priority.inc");
        }

}
