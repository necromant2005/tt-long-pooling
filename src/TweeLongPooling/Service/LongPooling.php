<?php

namespace TweeLongPooling\Service;

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

    protected $socks = [];
    
    /**
    * The default config values
    * 
    * @var array
    */
    protected $listen;
    protected $timePeriod;

    protected $timerCallback = null;

    // protected $stopServer = false;

    public function __construct(Array $listen = null, Integer $timePeriod = null)
    {
        $this->listen = $listen ? : [1337];
        $this->timePeriod = $timePeriod ? : 1;
    }

    public function setPeriodicTimerConfig(Array $config)
    {
        if($this->timerCallback === null) {
            $this->timerCallback = new TimerCallback;
        }
        $this->timerCallback->setConfig($config);

        return $this;
    }

    public function setPeriodicTimerCallback(Callable $callback)
    {
        if($this->timerCallback === null) {
            $this->timerCallback = new TimerCallback;
        }        
        $this->timerCallback->setCallback($callback);

        return $this;
    }

    /**
    *
    * Start server
    *
    */
    public function run()
    {
        if($this->timerCallback === null) {
            throw new LogicException('Wrong periodic timer configuration', 302);            
        }

        try {

            $this->conns = new SplObjectStorage;

            $loop = Factory::create();

            //periodic call
            $loop->addPeriodicTimer($this->timePeriod, function() use ($loop) {
                
                // if($this->stopServer) {
                //     $loop->stop();

                //     foreach ($this->socks as $sock) {
                //         $sock->shutdown();
                //     }
                // }
                
                call_user_func_array($this->timerCallback->getCallback(), [&$this->conns]);
            });

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

    // public function stop()
    // {
    //     $this->stopServer = true;
    // }    
}