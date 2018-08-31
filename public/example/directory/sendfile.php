<?php

use Batchelor\Storage\File;
use UUP\Site\Page\Service\StandardService;

// 
// Simple download script. For use with file example.
// 

class SendfilePage extends StandardService
{

        public function render()
        {
                // 
                // Test sending custom heeaders:
                // 
                $file = new File("/tmp/test.txt");
                $file->sendFile(true, ['X-Batchelor-Content' => 'File download example']);
        }

}
