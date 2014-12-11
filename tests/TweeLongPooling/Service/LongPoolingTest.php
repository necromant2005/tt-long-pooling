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
}