<?php

use Batchelor\Controller\Example\ExamplePage;

class InteractPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Interact", "interact.inc");
        }

}
