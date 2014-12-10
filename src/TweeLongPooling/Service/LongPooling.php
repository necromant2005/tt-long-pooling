<?php

namespace TweeLongPooling\Service;


use TweeLongPooling\Service\LongPooling\TimePeriod;
use TweeLongPooling\Service\LongPooling\ConnectionInfo;

use SplObjectStorage;
use Exception;
use LogicException;
use RuntimeException;
use React\EventLoop\Factory;
use React\Socket\Server;
use React\Socket\Connection;
use React\Http\RequestHeaderParser;

class LongPooling
{
    /**
    *
    * Pull of the connections
    *
    * @var SplObjectStorage
    */
    
    protected $conns;

    protected $loop;
    
    /**
    * The default config values
    * 
    * @var array
    */
    protected $listen;
    protected $timePeriod;

    protected $timerCallback = null;

    public function __construct(Array $listen = null, Integer $timePeriod = null, Array $configTimer)
    {
        $this->listen = $listen ? : [1337];
        $this->timePeriod = $timePeriod ? : 1;

        $this->conns = new SplObjectStorage;
        
        $this->timerCallback = new TimerCallback($configTimer, $this->conns);
        
        $this->loop = Factory::create();
    }

    public function setPeriodicTimerCallback(Callable $callback)
    {
        $this->loop->addPeriodicTimer($this->timePeriod, $this->timerCallback);
    }

    /**
    *
    * Start server
    *
    */
    public function run()
    {
        try {

            $this->setPeriodicTimerCallback();

            //listening of the ports
            foreach ($this->listen as $port) {
                
                $socket = new Server($loop);
                
                //initialize socket
                $socket->on('connection', function (Connection $conn) {
                    
                    $this->conns->attach($conn);
                    
                    $conn->on('data', function ($data, $conn) {
                        //$request insteadof React\Http\Request
                        list($request, $body) = (new RequestHeaderParser())->parseRequest($data);
                        $this->conns->attach($conn, new ConnectionInfo($request, $body));
                    });

                    $conn->on('end', function ($conn){
                        $this->conns->detach($conn);
                    });
                });

                $socket->listen($port);

                $this->socks[] = $socket;
            }

            $loop->run();

        } catch (Exception $e) {
            throw new RuntimeException("Server Run Exception", 301, $e);
        }
    }  
}