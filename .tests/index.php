<?php

include '../vendor/autoload.php';
include '../autoload_register.php';

use TweeLongPooling\Service\LongPooling;
use React\Socket\Connection;
use React\Http\Request;

$headerSt = "HTTP/1.1 200 OK\r\n" .
            "Content-Type text/html; charset=UTF-8\r\n".
            "\r\n";

$headerEnd = "\r\n\r\n";

$config = [
        'callsLimit' => 5,
        'callback' => 
            function (Connection $conn, Request $request, $data) { 
                   
                // throw new Exception("Error Processing Request", 1);
                                          
                return rand(1, 10) % 3 == 0 ? true : false;
            },

        'response' => [
            'done'    => $headerSt . 'done' . $headerEnd,
            'wait'    => $headerSt . 'wait' . $headerEnd,
            'error'   => $headerSt . 'error' . $headerEnd, 
        ],
    ]; 
    
(new LongPooling)->setPeriodicTimerConfig($config)->run();
