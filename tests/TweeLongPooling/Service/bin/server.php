#!/usr/bin/php
<?php

include '../../../../vendor/autoload.php';
include '../../../../src/TweeLongPooling/Service/LongPooling.php';

use TweeLongPooling\Service\LongPooling;
use React\Socket\Connection;
use React\Http\Request;

$service = new LongPooling;

$config = [
        'callsLimit' => 10,
        'callback' => 
            function (Connection $conn, Request $request, $data) {                                           
                return rand(1, 10) % 3 == 0 ? true : false;
            },

        'response' => [
            'done'    => 'done',
            'wait'    => 'wait',
            'error'   => 'error', 
        ],
    ]; 
    
$service->run($config);