<?php

use Batchelor\Controller\Example\ExamplePage;

class PersistancePage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Persistance Service", "persistance.inc");
        }

}
