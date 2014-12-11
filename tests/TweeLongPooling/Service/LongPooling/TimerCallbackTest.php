<?php

namespace TweeLongPooling\Service;

use PHPUnit_Framework_TestCase;
use stdClass;
use SplObjectStorage;
use Closure;
use Exception;

class TimerCallbackTest extends PHPUnit_Framework_TestCase
{
    protected $testString = 'test!@#$%^&*()_+"\'<>';

    public function testNoCallbackError()
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid callback', 201);

        new LongPooling\TimerCallback([], new SplObjectStorage);
    }

    public function testCallbackError()
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid callback', 202);

        new LongPooling\TimerCallback(
            [LongPooling\TimerCallback::OPT_CALLBACK => 'callback'], 
            new SplObjectStorage 
        );
    }
   
    public function testIteration()
    {
        $options = [
            LongPooling\TimerCallback::OPT_CALLS_LIMIT => 2,
            LongPooling\TimerCallback::OPT_CALLBACK => function() { return false; },
            LongPooling\TimerCallback::OPT_RESPONSE => [],
        ];  
    
        $mock = $this->getMock('stdClass', array('write', 'end'));
        
        $mock->expects($this->never())
                ->method('write');

        $mock->expects($this->never())
               ->method('end');        

        $storage = new SplObjectStorage;
        
        $storage->attach($mock, new LongPooling\ConnectionInfo(null, null));

        $timerCallback = new LongPooling\TimerCallback($options, $storage);

        call_user_func($timerCallback);
    }    

    /**
     * @dataProvider callbacksAndResults
     *
     * @depends testIteration
     */    
    public function testResponse(Closure $callback, $type)
    {
        $options = [
            LongPooling\TimerCallback::OPT_CALLS_LIMIT => 1,
            LongPooling\TimerCallback::OPT_CALLBACK => $callback,
            LongPooling\TimerCallback::OPT_RESPONSE => [
                LongPooling\TimerCallback::OPT_RESPONSE_DONE    => 'done' . $this->testString,
                LongPooling\TimerCallback::OPT_RESPONSE_WAIT    => 'wait' . $this->testString,
                LongPooling\TimerCallback::OPT_RESPONSE_ERROR   => 'error' . $this->testString, 
            ],
        ];  
    
        $mock = $this->getMock('stdClass', array('write', 'end'));
        
        $mock->expects($this->once())
                ->method('write')
                ->will($this->returnCallback(function($data) { echo $data; }));
        
        $mock->expects($this->once())
               ->method('end');        

        $storage = new SplObjectStorage;
        
        $storage->attach($mock, new LongPooling\ConnectionInfo(null, null));

        $timerCallback = new LongPooling\TimerCallback($options, $storage);

        $this->expectOutputString($options[LongPooling\TimerCallback::OPT_RESPONSE][$type]);

        call_user_func($timerCallback);
    }

    public function callbacksAndResults()
    {
        return [
            [ function() { return true; }, LongPooling\TimerCallback::OPT_RESPONSE_DONE ],
            [ function() { return false; }, LongPooling\TimerCallback::OPT_RESPONSE_WAIT ] ,
            [ function() { return $this->testString; }, LongPooling\TimerCallback::OPT_RESPONSE_ERROR ],
            [ function() { throw new Exception(); }, LongPooling\TimerCallback::OPT_RESPONSE_ERROR ],
        ];
    }
}