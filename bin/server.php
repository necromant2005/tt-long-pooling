<?php

include '../vendor/autoload.php';
include '../autoload_register.php';

use TweeLongPooling\Service\LongPooling;
use React\Socket\Connection;
use React\Http\Request;
use Exception;

 $headerSt = "HTTP/1.1 200 OK\r\n" .
            "Content-Type text/html; charset=UTF-8\r\n".
            "\r\n";

$headerEnd = "\r\n\r\n";

$config = [
    LongPooling\TimerCallback::OPT_CALLS_LIMIT => 10,
    LongPooling\TimerCallback::OPT_CALLBACK => 
        function () {                        
            return false; // LongPooling\TimerCallback::OPT_RESPONSE_WAIT
            // return true; // LongPooling\TimerCallback::OPT_RESPONSE_DONE
            // throw new Exception; // LongPooling\TimerCallback::OPT_RESPONSE_ERROR
        },
    LongPooling\TimerCallback::OPT_RESPONSE => [
        LongPooling\TimerCallback::OPT_RESPONSE_DONE    => $headerSt . 'done' . $headerEnd,
        LongPooling\TimerCallback::OPT_RESPONSE_WAIT    => $headerSt . 'wait' . $headerEnd,
        LongPooling\TimerCallback::OPT_RESPONSE_ERROR   => $headerSt . 'error' . $headerEnd, 
    ],
];  
    
(new LongPooling($config))->run();
