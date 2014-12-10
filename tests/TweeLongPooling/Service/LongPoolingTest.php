<?php

namespace TweeLongPooling\Service;

use PHPUnit_Framework_TestCase;

class LongPoolingTest extends PHPUnit_Framework_TestCase
{
    public function testNotConfiguredError()
    {
        $this->setExpectedException('LogicException', 'Wrong periodic timer configuration', 302);
        
        // (new LongPooling)->run();
    } 

    public function testCallbackError()
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid callback', 201);
        
        // (new LongPooling)->setPeriodicTimerConfig(['callback' => null])->run();
    } 
}