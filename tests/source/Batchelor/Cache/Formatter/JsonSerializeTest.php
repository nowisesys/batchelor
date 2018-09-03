<?php

namespace Batchelor\Cache\Formatter;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2018-09-01 at 18:19:17.
 */
class JsonSerializeTest extends \PHPUnit_Framework_TestCase
{

        /**
         * @var JsonSerialize
         */
        protected $object;

        /**
         * Sets up the fixture, for example, opens a network connection.
         * This method is called before a test is executed.
         */
        protected function setUp()
        {
                $this->object = new JsonSerialize;
        }

        /**
         * Tears down the fixture, for example, closes a network connection.
         * This method is called after a test is executed.
         */
        protected function tearDown()
        {
                
        }

        /**
         * @covers Batchelor\Cache\Formatter\Json::onRead
         */
        public function testOnRead()
        {
                $expect = "test1";
                $svalue = json_encode($expect);
                $actual = $this->object->onRead($svalue);
                $this->assertNotNull($actual);
                $this->assertTrue(is_string($actual));
                $this->assertEquals($actual, $expect);

                $expect = false;
                $svalue = json_encode($expect);
                $actual = $this->object->onRead($svalue);
                $this->assertNotNull($actual);
                $this->assertTrue(is_bool($actual));
                $this->assertEquals($actual, $expect);

                $expect = ["k1" => "v1", "k2" => false];
                $svalue = json_encode($expect);
                $actual = (array) $this->object->onRead($svalue);
                $this->assertNotNull($actual);
                $this->assertTrue(is_array($actual));
                $this->assertEquals($actual, $expect);
        }

        /**
         * @covers Batchelor\Cache\Formatter\Json::onSave
         */
        public function testOnSave()
        {
                $svalue = "test1";
                $expect = json_encode($svalue);
                $actual = $this->object->onSave($svalue);
                $this->assertNotNull($actual);
                $this->assertTrue(is_string($actual));
                $this->assertEquals($actual, $expect);

                $svalue = false;
                $expect = json_encode($svalue);
                $actual = $this->object->onSave($svalue);
                $this->assertNotNull($actual);
                $this->assertTrue(is_string($actual));
                $this->assertEquals($actual, $expect);

                $svalue = ["k1" => "v1", "k2" => false];
                $expect = json_encode($svalue);
                $actual = $this->object->onSave($svalue);
                $this->assertNotNull($actual);
                $this->assertTrue(is_string($actual));
                $this->assertEquals($actual, $expect);
        }

        /**
         * @covers Batchelor\Cache\Formatter\Json::setOptions
         */
        public function testSetOptions()
        {
                $options = ['read' => ['k1' => 'v2']];
                $this->object->setOptions($options);
        }

        /**
         * @covers Batchelor\Cache\Formatter\Json::getOptions
         */
        public function testGetOptions()
        {
                $options = [
                        'read' => [
                                'assoc'   => false,
                                'depth'   => 512,
                                'options' => 0
                        ],
                        'save' => [
                                'depth'   => 512,
                                'options' => 0
                        ]
                ];

                $expect = $options;
                $actual = $this->object->getOptions();
                $this->assertNotNull($actual);
                $this->assertTrue(is_array($actual));
                $this->assertEquals($actual, $expect);

                $expect = $options['read'];
                $actual = $this->object->getOptions('read');
                $this->assertNotNull($actual);
                $this->assertTrue(is_array($actual));
                $this->assertEquals($actual, $expect);

                $expect = $options['save'];
                $actual = $this->object->getOptions('save');
                $this->assertNotNull($actual);
                $this->assertTrue(is_array($actual));
                $this->assertEquals($actual, $expect);

                $options = array_merge(['read' => ['k1' => 'v2']], $options);
                $this->object->setOptions($options);

                $expect = $options;
                $actual = $this->object->getOptions();
                $this->assertNotNull($actual);
                $this->assertTrue(is_array($actual));
                $this->assertEquals($actual, $expect);

                $expect = $options['read'];
                $actual = $this->object->getOptions('read');
                $this->assertNotNull($actual);
                $this->assertTrue(is_array($actual));
                $this->assertEquals($actual, $expect);

                $expect = $options['save'];
                $actual = $this->object->getOptions('save');
                $this->assertNotNull($actual);
                $this->assertTrue(is_array($actual));
                $this->assertEquals($actual, $expect);
        }

}