<?php

namespace Batchelor\Queue\Task\Execute;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2018-09-21 at 01:09:09.
 */
class CommandTest extends \PHPUnit_Framework_TestCase
{

        /**
         * @var Command
         */
        protected $object;

        /**
         * Sets up the fixture, for example, opens a network connection.
         * This method is called before a test is executed.
         */
        protected function setUp()
        {
                $this->object = new Command("ls -l /tmp");
        }

        /**
         * Tears down the fixture, for example, closes a network connection.
         * This method is called after a test is executed.
         */
        protected function tearDown()
        {
                
        }

        /**
         * @covers Batchelor\Queue\Task\Execute\Command::getOutput
         */
        public function testGetOutput()
        {
                $this->object->setBlocking(1, true);
                $actual = $this->object->getOutput();

                $this->assertNotNull($actual);
                $this->assertTrue(is_string($actual));
                $this->assertTrue(strlen($actual) > 0);
        }

        /**
         * @covers Batchelor\Queue\Task\Execute\Command::getError
         */
        public function testGetError()
        {
                $actual = $this->object->getError();

                $this->assertNotNull($actual);
                $this->assertTrue(is_string($actual));
                $this->assertTrue(strlen($actual) == 0);

                $this->object = new Command("ls -l /tmp1");

                $actual = $this->object->getError();

                $this->assertNotNull($actual);
                $this->assertTrue(is_string($actual));
                $this->assertTrue(strlen($actual) > 0);
        }

        /**
         * @covers Batchelor\Queue\Task\Execute\Command::hasOutput
         */
        public function testHasOutput()
        {
                $expect = true;
                $actual = $this->object->hasOutput();

                $this->assertNotNull($actual);
                $this->assertTrue(is_bool($actual));
                $this->assertEquals($expect, $actual);
        }

        /**
         * @covers Batchelor\Queue\Task\Execute\Command::isFinished
         */
        public function testIsFinished()
        {
                $this->object->setBlocking(1, true);

                $expect = false;
                $actual = $this->object->isFinished();

                $this->assertNotNull($actual);
                $this->assertTrue(is_bool($actual));
                $this->assertEquals($expect, $actual);

                while (($this->object->getOutput()));

                $expect = true;
                $actual = $this->object->isFinished();

                $this->assertNotNull($actual);
                $this->assertTrue(is_bool($actual));
                $this->assertEquals($expect, $actual);
        }

}
