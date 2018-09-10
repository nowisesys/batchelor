<?php

namespace Batchelor\Data\Structure;

require(__DIR__ . '/AssocArrayTester.php');

class MyAssocArrayHeap extends AssocArrayHeap
{

        protected function compare($object1, $object2)
        {
                return $object1->getValue() - $object2->getValue();
        }

}

class AssocArrayHeapTest extends AssocArrayTester
{

        protected function setUp()
        {
                $this->object = new MyAssocArrayHeap();
        }

}
