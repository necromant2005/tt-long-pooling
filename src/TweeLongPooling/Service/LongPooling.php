<?php

namespace TweeLongPooling\Service;

use SplObjectStorage;
use Exception;
use Closure;
use React\EventLoop\Factory;
use React\Socket\Server;
use React\Socket\Connection;
use React\Http\RequestHeaderParser;

class LongPooling
{
    const COUNT = 'count';

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
    private $config = [
                'listen' => [1337], 
                'callsLimit' => 20, 
                'timePeriod' => 1,
                'response' => ['done' => 'done', 'wait' => 'wait', 'error' => 'error'] ];

    /**
    *
    * Start server
    *
    * @param array $config
    */
    public function run(Array $config)
    {
        //callback validation
        if(!array_key_exists('callback', $config) or !($config['callback'] instanceof Closure)) {
            echo "Invalid callback";
            return;
        }

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
                            
                            $data[self::COUNT]++;

                            //response
                            $responseCallback = call_user_func_array($this->config['callback'], array($conn, $data['request'], $data['data']));

                            if($data[self::COUNT] >= $this->config['callsLimit'] or $responseCallback === true) {

                                $this->conns->attach($conn, $data);                                

                                //sent response
                                $buffer = ( $responseCallback === true ? 
                                            $this->config['response']['done'] : 
                                            ( $responseCallback === false ? 
                                                $this->config['response']['wait'] : 
                                                $this->config['response']['error'] ) );
                                                            
                                $conn->write($buffer);
                                $conn->end();
                            } else {
                                $this->conns->attach($conn, $data);
                            }
                        }

                    } catch (Exception $e) {
                        if($conn) {
                            $conn->write($this->config['response']['error']);
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
                        $this->fArr[] = $conn;
                        $this->conns->attach($conn, [   self::COUNT => 0, 
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
}