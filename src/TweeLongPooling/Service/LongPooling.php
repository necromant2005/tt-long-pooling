<?php

namespace TweeLongPooling\Service;

use SplObjectStorage;
use Exception;
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
    
    /**
    * The default config values
    * 
    * @var array
    */
    private $config = ['listen' => [1337], 'callsLimit' => 20, 'timePeriod' => 1];

    /**
    *
    * Start server
    *
    * @param array $config
    */
    public function run(Array $config)
    {

        try {

            $this->conns = new SplObjectStorage;

            $this->config = array_merge($this->config, $config);

            $loop = Factory::create();

            //periodic call
            $loop->addPeriodicTimer($this->config['timePeriod'], function() use ($loop) {

                //callback call
                foreach($this->conns as $conn) {

                    try {
                                                
                        if($data = $this->conns->getInfo()) {
                            
                            $data['count']++;

                            $return = call_user_func_array($this->config['callback'], array($conn, $data['request'], $data['data']));

                            if($data['count'] >= $this->config['callsLimit'] or $return === true) {

                                $this->conns->detach($conn);
                                
                                $data['finished'] = true;

                                //sent response
                                $buffer = ( $return === true ? 
                                            $this->config['response']['done'] : 
                                            ( $return === false ? 
                                                $this->config['response']['wait'] : 
                                                $this->prepareError('Invalid callback responce') ) );
                                                            
                                $conn->write($buffer);
                                $conn->end();
                            } else {
                                $this->conns->attach($conn, $data);
                            }
                        }

                    } catch (Exception $e) {
                        if($conn) {
                            $conn->write($this->prepareError($e->getMessage(), $e->getCode()));
                            $conn->end(); 
                        }
                    }
                }
            });

            //listening of the ports
            foreach ($this->config['listen'] as $port) {
                
                $socket = new Server($loop);
                
                //initialize socket
                $socket->on('connection', function (Connection $conn) {
                    
                    $this->conns->attach($conn);
                    
                    $conn->on('data', function ($data, $conn) {
                        //$request typeof React\Http\Request
                        list($request, $body) = (new RequestHeaderParser())->parseRequest($data);
                        
                        $this->conns->attach($conn, [   'count' => 0, 
                                                        'request' => $request, 
                                                        'data' => $body ] );
                    });

                    $conn->on('end', function ($conn){
                        $this->conns->detach($conn);
                    });
                });

                $socket->listen($port);
            }

            $loop->run();

        } catch (Exception $e) {
            echo "Server Run Exception: " . $e->getMessage() . ' / ' . $e->getCode();
        }
    }

    //format error message
    protected function prepareError($message, $code = null)
    {
        $errorBuffer = $this->config['response']['error'];

        if($code !== null) {
            $errorBuffer = str_replace('{{ERROR_CODE}}', $code, $errorBuffer);
        }

        return str_replace('{{ERROR_MESSAGE}}', $message, $errorBuffer);
    }
}