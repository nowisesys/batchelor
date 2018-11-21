<?php

use Batchelor\Controller\Example\ExamplePage;

class CommandPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Command", "command.inc");
        }

}
