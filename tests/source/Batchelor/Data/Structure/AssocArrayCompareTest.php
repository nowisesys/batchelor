<?php

namespace Batchelor\Data\Structure;

require(__DIR__ . '/AssocArrayTester.php');

class AssocArrayCompareTest extends AssocArrayTester
{

        protected function setUp()
        {
                $this->object = new AssocArrayCompare(static function($object1, $object2) {
                        return $object1->getValue() - $object2->getValue();
                });
        }

        /**
         * @covers Batchelor\Structure\AssocArrayCompare::setCompare
         */
        public function testSetCompare()
        {
                $this->object->setCompare(function() {});
                
                $this->setExpectedException(\TypeError::class);
                $this->object->setCompare(null);
        }

}
