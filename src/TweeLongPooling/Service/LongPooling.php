<?php

namespace TweeLongPooling\Service;

use SplObjectStorage;
use Exception;
use RuntimeException;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\Socket\Server;
use React\Socket\Connection;
use React\Http\RequestHeaderParser;

class LongPooling
{
    /**
    * Pull of the connections
    *
    * @var SplObjectStorage
    */    
    protected $conns = null;

    /**
    * Events loop
    *
    * @var LoopInterface
    */    
    protected $loop = null;
    
    /**
    * Default ports
    * 
    * @var Array
    */
    protected $listen = [1337];

    /**
    * Default time period for periodic timer
    * 
    * @var Integer
    */
    protected $timePeriod = 1;
    
    /**
    * Timmer callback
    * 
    * @var TimerCallback
    */
    protected $timerCallback = null;

    /**
    * Constructor
    * 
    * @param Array $configTimer
    * @param Array $listen
    * @param Integer $timePeriod
    */
    public function __construct(Array $configTimer, Array $listen = null, $timePeriod = null)
    {
        $this->listen = $listen ? : $this->listen;
        $this->timePeriod = (int)$timePeriod ? (int)$timePeriod : $this->timePeriod;

        $this->conns = new SplObjectStorage;

        $this->setPeriodicTimerCallback(new LongPooling\TimerCallback($configTimer, $this->conns));
        
        $this->loop = Factory::create();
    }

    /**
    * Set timmer callback
    * 
    * @param TimerCallback $timerCallback
    */
    public function setPeriodicTimerCallback(LongPooling\TimerCallback $timerCallback)
    {
        $this->timerCallback = $timerCallback;
    }

    /**
    * Get timmer callback
    * 
    * @return TimerCallback
    */
    public function getPeriodicTimerCallback()
    {
        return $this->timerCallback;
    }

    /**
    * Set event loop
    * 
    * @param LoopInterface $loop
    */
    public function setLoop(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    /**
    * Get event loop
    * 
    * @return LoopInterface
    */
    public function getLoop()
    {
        return $this->loop;
    }

    /**
    * Start server
    *
    * @throws RuntimeException
    */
    public function run()
    {
        try {

            $this->loop->addPeriodicTimer($this->timePeriod, $this->getPeriodicTimerCallback());

            //listening of the ports
            foreach ($this->listen as $port) {
                
                $socket = new Server($this->loop);
                
                //initialize socket
                $socket->on('connection', function (Connection $conn) {
                    
                    $this->conns->attach($conn);
                    
                    $conn->on('data', function ($data, $conn) {
                        //$request insteadof React\Http\Request
                        list($request, $body) = (new RequestHeaderParser())->parseRequest($data);
                        $this->conns->attach($conn, new LongPooling\ConnectionInfo($request, $body));
                    });

                    $conn->on('end', function ($conn){
                        $this->conns->detach($conn);
                    });
                });

                $socket->listen($port);
            }

            $this->loop->run();

        } catch (Exception $e) {
            throw new RuntimeException("Server Run Exception", 301, $e);
        }
    }  
}
