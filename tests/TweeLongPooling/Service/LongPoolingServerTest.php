<?php

namespace TweeLongPooling\Service;

use PHPUnit_Framework_TestCase;

class LongPoolingDoneTest extends PHPUnit_Framework_TestCase
{
    protected static $longPooling;

    public static function setUpBeforeClass()
    {
        $headerSt = "HTTP/1.1 200 OK\r\n" .
                    "Content-Type text/html; charset=UTF-8\r\n".
                    "\r\n";

        $headerEnd = "\r\n\r\n";

        $config = [
                'callsLimit' => 10,
                'callback' => 
                    function (Connection $conn, Request $request, $data) { 
                                                                             
                        return rand(1, 10) % 3 == 0 ? true : false;
                    },

                'response' => [
                    'done'    => $headerSt . 'done' . $headerEnd,
                    'wait'    => $headerSt . 'wait' . $headerEnd,
                    'error'   => $headerSt . 'error' . $headerEnd, 
                ],
            ]; 
            
        static::$longPooling = new LongPooling;
        static::$longPooling->setPeriodicTimerConfig($config)->run();
    }

    public function testDoneResponse()
    {

        $this->expectOutputString("done");

        //start client

        echo 'done';
    }

    /**
     * @depends testDoneResponse
     */
    public function testWaitResponse()
    {
        //reinit server 'wait'
        
        $this->expectOutputString("wait");

        //start client

        echo 'wait';
    }

    /**
     * @depends testWaitResponse
     */
    public function testErrorResponse()
    {
        //reinit server 'error'

        $this->expectOutputString("error");

        //start client

        echo 'error';
    } 

    public static function tearDownAfterClass()
    {
        static::$longPooling->stop();
    }
}