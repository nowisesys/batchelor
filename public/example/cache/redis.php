<?php

use Batchelor\Web\ExamplePage;

class RedisPage extends ExamplePage
{

        public function __construct()
        {
                parent::__construct("Redis server", "redis.inc", function() {
                        include("support/footer.inc");
                });
        }

}
