<?php

use Batchelor\Web\ExamplePage;

class WorkerPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Worker", "worker.inc");
        }

}
