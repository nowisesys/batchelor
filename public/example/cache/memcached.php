<?php

use Batchelor\Web\ExamplePage;

class MemcachedPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Memcached server", "memcached.inc", function() {
                        include("support/footer.inc");
                });
        }

}
