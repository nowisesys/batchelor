<?php

/*
 * Copyright (C) 2018 Anders Lövgren (Nowise Systems)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace Batchelor\Data\Structure;

use ArrayIterator;

class MyObject
{

        private $_key;
        private $_val;

        public function __construct($key, $val)
        {
                $this->_key = $key;
                $this->_val = $val;
        }

        public function getValue()
        {
                return $this->_val;
        }

}

/**
 * Base class for accosiative array test.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
abstract class AssocArrayTester extends \PHPUnit_Framework_TestCase
{

        /**
         * @var AssocArrayHeap
         */
        protected $object;

        /**
         * @covers Batchelor\Structure\AssocArrayHeap::addObject
         */
        public function testAddObject()
        {
                $this->assertTrue($this->object->isEmpty());

                $this->object->addObject("key1", new MyObject("k1", 100), false);
                $this->object->addObject("key2", new MyObject("k2", 50), false);
                $this->object->addObject("key3", new MyObject("k3", 200), false);

                $this->assertFalse($this->object->isEmpty());
        }

        /**
         * @covers Batchelor\Structure\AssocArrayHeap::removeObject
         */
        public function testRemoveObject()
        {
                $this->assertTrue($this->object->isEmpty());
                $this->object->addObject("key1", new MyObject("k1", 100), false);
                $this->assertFalse($this->object->isEmpty());
                $this->object->removeObject("key1");
                $this->assertTrue($this->object->isEmpty());
        }

        /**
         * @covers Batchelor\Structure\AssocArrayHeap::isEmpty
         */
        public function testIsEmpty()
        {
                $this->assertTrue($this->object->isEmpty());
                $this->object->addObject("key1", new MyObject("k1", 100), false);
                $this->assertFalse($this->object->isEmpty());
        }

        /**
         * @covers Batchelor\Structure\AssocArrayHeap::hasObject
         */
        public function testHasObject()
        {
                $this->assertFalse($this->object->hasObject("key1"));
                $this->object->addObject("key1", new MyObject("k1", 100), false);
                $this->assertTrue($this->object->hasObject("key1"));
        }

        /**
         * @covers Batchelor\Structure\AssocArrayHeap::getObject
         */
        public function testGetObject()
        {
                $object = new MyObject("k1", 100);
                $this->object->addObject("key1", $object);

                $expect = $object;
                $actual = $this->object->getObject("key1");
                $this->assertNotNull($actual);
                $this->assertEquals($expect, $actual);
                $this->assertSame($expect, $actual);
        }

        /**
         * @covers Batchelor\Structure\AssocArrayHeap::getObjects
         */
        public function testGetObjects()
        {
                $expect = [];
                $actual = $this->object->getObjects();
                $this->assertNotNull($actual);
                $this->assertEquals($expect, $actual);

                $object = new MyObject("k1", 100);
                $this->object->addObject("key1", $object);

                $expect = ['key1' => $object];
                $actual = $this->object->getObjects();
                $this->assertNotNull($actual);
                $this->assertEquals($expect, $actual);
        }

        /**
         * @covers Batchelor\Structure\AssocArrayHeap::setSorted
         */
        public function testSetSorted()
        {
                $this->object->addObject("key1", new MyObject("k1", 100), false);
                $this->object->addObject("key2", new MyObject("k1", 50), false);
                $this->object->addObject("key3", new MyObject("k1", 200), false);
                $this->object->addObject("key4", new MyObject("k1", 125), false);

                $expect = ["key1", "key2", "key3", "key4"];
                $actual = array_keys($this->object->getObjects());
                $this->assertNotNull($actual);
                $this->assertEquals($expect, $actual);

                $this->object->setSorted();

                $expect = ["key2", "key1", "key4", "key3"];
                $actual = array_keys($this->object->getObjects());
                $this->assertNotNull($actual);
                $this->assertEquals($expect, $actual);
        }

        /**
         * @covers Batchelor\Structure\AssocArrayHeap::getIterator
         */
        public function testGetIterator()
        {
                $this->object->addObject("key1", new MyObject("k1", 100), false);
                $this->object->addObject("key2", new MyObject("k1", 50), false);
                $this->object->addObject("key3", new MyObject("k1", 200), false);
                $this->object->addObject("key4", new MyObject("k1", 125), false);

                $actual = $this->object->getIterator();
                $this->assertNotNull($actual);
                $this->assertTrue($actual instanceof ArrayIterator);
        }

}
