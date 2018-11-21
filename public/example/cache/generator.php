<?php

use Batchelor\Controller\Example\ExamplePage;

class GeneratorPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Shared memory ID generator", "generator.inc");
        }

}
