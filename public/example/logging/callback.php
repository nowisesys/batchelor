<?php

use Batchelor\Controller\Example\ExamplePage;

class CallbackPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Callback", "callback.inc");
        }

}
