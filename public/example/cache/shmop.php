<?php

use Batchelor\Web\ExamplePage;

class ShmopPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Shared memory (shmop) cache", "shmop.inc", function() {
                        include("support/footer.inc");
                });
        }

        public function printContent()
        {
                parent::printContent();
                include('shmop-ulimit.inc');
        }
}
