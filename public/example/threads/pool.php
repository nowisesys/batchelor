<?php

// 
// Test using the pthreads extension.
// 
// https://github.com/krakjoe/pthreads
// http://pecl.php.net/package/pthreads
// 

if (PHP_SAPI != 'cli') {
        die("This example can only be run in CLI mode");
}

if (extension_loaded("pthreads")) {
        printf("Running example using native extension\n");
} else {
        printf("Running example using polyfills\n");
}

class Square extends Thread
{

        private $_num;
        private $_res;

        public function __construct(int $num)
        {
                $this->_num = $num;
                $this->_res = -1;
        }

        public function run()
        {
                printf("[%f] Runnning on thread %d\n", microtime(true), self::getCurrentThreadId());
                $this->_res = $this->_num * $this->_num;
                printf("[%f] The square of %d is %d\n", microtime(true), $this->_num, $this->_res);
                sleep(1);
        }

        public function isComplete(): bool
        {
                return $this->_res != -1;
        }

}

$pool = new Pool(3);
printf("[%f] There are %d tasks in pool\n", microtime(true), $pool->collect());

for ($i = 0; $i <= 12; ++$i) {
        $pool->submit(new Square($i));
        printf("[%f] There are %d tasks in pool\n", microtime(true), $pool->collect());
}

while ($pool->collect());
printf("[%f] There are %d tasks in pool\n", microtime(true), $pool->collect());

$pool->shutdown();
