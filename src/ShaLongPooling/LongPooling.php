<?php

namespace ShaLongPooling;

use SplObjectStorage;
use Exception;
use React\EventLoop\Factory;
use React\Socket\Server;
use React\Socket\Connection;

class LongPooling
{
    CONST RESPONSE_DONE     = 'DONE';
    CONST RESPONSE_WAIT     = 'WAIT';
    CONST RESPONSE_ERROR    = 'ERROR';
    
    /**
    * Pull of the connections
    *
    * @var SplObjectStorage
    */
    
    protected $conns;

    /**
    * Pull of the connections to be closed in the next timer iteration
    *
    * @var array
    */    
    protected $conns2Close = [];
    
    /**
    * The default config values
    * 
    * @var array
    */
    private $config = ['listen' => 1337, 'callsLimit' => 20, 'timePeriod' => 1];

    public function __construct() {
        $this->conns = new SplObjectStorage;
    }

    /**
    *
    * Start server
    *
    * @param array $config
    */
    public function run($config)
    {
        $this->config = array_merge($this->config, $config);

        $loop = Factory::create();
        $socket = new Server($loop);

        //initialize socket
        $socket->on('connection', function (Connection $conn) {
            
            $this->conns->attach($conn);
            
            $conn->on('data', function ($data, $conn){
                $query = ['count' => 0, 'data' => $data];
                $this->conns->attach($conn, $query);
            });

            $conn->on('end', function () use ($conn) {
                $this->conns->detach($conn);
            });
        });

        //periodic call
        $loop->addPeriodicTimer($this->config['timePeriod'], function() use ($socket) {
            
            //close unused connections
            while($connClose = array_shift($this->conns2Close)) {
                $connClose->handleClose();
            }

            //callback call
            foreach($this->conns as $conn) {
                
                $data = $this->conns->offsetGet($conn);
                
                if($data and array_key_exists('count', $data)) {
                    $data['count']++;
                    
                    $return = call_user_func_array($this->config['callback'], array($conn, $data['data']));

                    if($data['count'] >= $this->config['callsLimit'] or $return === true) {
                        $this->conns->detach($conn);
                        $this->conns2Close[] = $conn;

                        //sent response
                        $buffer =   
                            "HTTP/1.1 200 OK\r\n" .
                            "Content-Head: application/json\r\n" .
                            "\r\n" .
                            json_encode(['status' => 
                                ($return === true ? 
                                    self::RESPONSE_DONE : 
                                    ($return === false ? 
                                        self::RESPONSE_WAIT : 
                                        self::RESPONSE_ERROR)) ]) .
                            "\r\n\r\n";
                    
                        $conn->write($buffer);
                        $conn->end();
                    } else {
                        $this->conns->attach($conn, $data);
                    }
                }
            }
        });

        //start server
        $socket->listen($this->config['listen']);
        $loop->run();
    }
}