<?php

include '../vendor/autoload.php';
include '../autoload_register.php';

use TweeLongPooling\Service\LongPooling;
use React\Socket\Connection;
use React\Http\Request;

class SomeClass
{
    public function write($data)
    {
        echo $data;
    }
    public function end() {}
}

    $storage = new SplObjectStorage;
    
    $storage->attach(new SomeClass, new LongPooling\ConnectionInfo(null, null));
        
    $options = [
        LongPooling\TimerCallback::OPT_CALLS_LIMIT => 1,
        LongPooling\TimerCallback::OPT_CALLBACK => 
            function () {                        
                return false;
            },
        LongPooling\TimerCallback::OPT_RESPONSE => [
            LongPooling\TimerCallback::OPT_RESPONSE_DONE    => 'done',
            LongPooling\TimerCallback::OPT_RESPONSE_WAIT    => 'wait',
            LongPooling\TimerCallback::OPT_RESPONSE_ERROR   => 'error', 
        ],
    ];         

    $timerCallback = new LongPooling\TimerCallback($options, $storage);

    call_user_func($timerCallback);







die;

$headerSt = "HTTP/1.1 200 OK\r\n" .
            "Content-Type text/html; charset=UTF-8\r\n".
            "\r\n";

$headerEnd = "\r\n\r\n";

$config = [
        'callsLimit' => 5,
        'callback' => 
            function (Connection $conn, Request $request, $data) { 
                   
                throw new Exception("Error Processing Request", 1); 

                return rand(1, 10) % 3 == 0 ? true : false;
            },

        'response' => [
            'done'    => $headerSt . 'done' . $headerEnd,
            'wait'    => $headerSt . 'wait' . $headerEnd,
            'error'   => $headerSt . 'error' . $headerEnd, 
        ],
    ]; 
    
(new LongPooling($config))->run();
