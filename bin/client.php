<?php

include '../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();

$client = @stream_socket_client('tcp://127.0.0.1:1337');

if($client) {
    
    $conn = new React\Socket\Connection($client, $loop);

    $conn->on('data', function ($data, $conn) use ($loop) {
        list($request, $body) = (new React\Http\RequestHeaderParser())->parseRequest($data);
        echo trim($body);
        $conn->close();
        $loop->stop();
    });

    $conn->pipe(new React\Stream\Stream(fopen('php://stdout', 'w'), $loop));

    $conn->pipe(new React\Stream\Stream(fopen('php://stdin', 'r'), $loop));

    $data = "HTTP/1.1 200 OK\r\n" .
                "Content-Type text/html; charset=UTF-8\r\n\r\n" .
                "Hello world" .
                "\r\n\r\n";

    $conn->write($data);

    $loop->run();
} else {
    echo 'Connection error';
}