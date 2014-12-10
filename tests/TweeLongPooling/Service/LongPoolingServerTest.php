<?php

namespace TweeLongPooling\Service;

use PHPUnit_Framework_TestCase;

class LongPoolingDoneTest extends PHPUnit_Framework_TestCase
{
    public function testDoneResponse()
    {
        $this->expectOutputString("done");

        echo 'done';
    }

    public function testWaitResponse()
    {        
        $this->expectOutputString("wait");

        echo 'wait';
    }

    public function testErrorResponse()
    {
        $this->expectOutputString("error");

        echo 'error';
    } 
}