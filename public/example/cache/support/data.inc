<?php

class ExampleType
{

        private $_k1;
        private $_k2;

        public function __construct()
        {
                $this->_k1 = 'v1';
                $this->_k2 = 888;
        }

}

return [
        'data1' => 'value1',
        'data2' => [
                'k1' => 'v1',
                'k2' => 'v2',
                'k3' => [
                        'k4' => 1.0,
                        'k5' => 650,
                        'k6' => 'v6'
                ],
                'k7' => 'v7',
        ],
        'data3' => new ExampleType()
];
