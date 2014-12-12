<?php

namespace TweeLongPooling\Service;

use PHPUnit_Framework_TestCase;

class ConnectionInfoTest extends PHPUnit_Framework_TestCase
{
    protected $testString = 'test!@#$%^&*()_+"\'<>';

    public function testGetCounter()
    {
        $connInfo  = new LongPooling\ConnectionInfo(null, '');

        $this->assertEquals(0, $connInfo->getCounter());
    }
    
    public function testGetTime()
    {
        $connInfo  = new LongPooling\ConnectionInfo(null, '');

        $this->assertEquals(time(), $connInfo->getTime(), '', 1);
    }

    /**
    * @depends testGetCounter
    */
    public function testIncrementCounter()
    {
        $connInfo = new LongPooling\ConnectionInfo(null, '');

        $connInfo->incrementCounter(); 

        $this->assertEquals(1, $connInfo->getCounter());
    }

    /**
    * @depends testGetCounter
    */
    public function testDecrementGetCounter()
    {
        $connInfo = new LongPooling\ConnectionInfo(null, '');
        
        $connInfo->decrementCounter();
        
        $this->assertEquals(-1, $connInfo->getCounter());
    }

    public function testGetRequest()
    {
        $connInfo  = new LongPooling\ConnectionInfo($this->testString, '');

        $this->assertEquals($this->testString, $connInfo->getRequest());
    } 

    public function testGetData()
    {
        $connInfo  = new LongPooling\ConnectionInfo(null, $this->testString);

        $this->assertEquals($this->testString, $connInfo->getData());
    }  
}