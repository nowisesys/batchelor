<?php

use Batchelor\Web\ExamplePage;

class UptimePage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Uptime", "uptime.inc");

                if (!extension_loaded("expect")) {
                        throw new RuntimeException("The expect extension is required for this example");
                }
        }

}
