<?php

namespace TweeLongPooling\Service;

use PHPUnit_Framework_TestCase;

class LongPoolingTest extends PHPUnit_Framework_TestCase
{
    public function testNoCallbackError()
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid callback', 201);
        
        (new LongPooling([]))->run();
    }     

    public function testCallbackError()
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid callback', 202);
        
        (new LongPooling([LongPooling\TimerCallback::OPT_CALLBACK => null]))->run();
    } 
    
    public function testLoop()
    {
        $options = [
            LongPooling\TimerCallback::OPT_CALLBACK => function() { return false; },
        ];  

        $timePeriod = 11;

        $service = new LongPooling($options, [], $timePeriod);
        
        $mock = $this->getMock(get_class($service->getLoop()));
        
        $mock->expects($this->once())->method('run');

        $mock->expects($this->once())
            ->method('addPeriodicTimer')
            ->with($this->equalTo($timePeriod),
                $this->equalTo($service->getPeriodicTimerCallback()));

        $service->setLoop($mock);

        $service->run();
    }
}