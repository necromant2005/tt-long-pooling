<?php 

namespace TweeLongPooling\Service\LongPooling;

use Exception;
use InvalidArgumentException;
use SplObjectStorage;

class TimerCallback
{
    private $config = [
                'callsLimit' => 20, 
                'response' => ['done' => 'done', 'wait' => 'wait', 'error' => 'error'] ];

    private $conns;

    public function __construct(Array $config, SplObjectStorage $conns) 
    {
        if(!array_key_exists('callback', $config) or !is_callable($config['callback'])) {
            throw new InvalidArgumentException('Invalid callback', 201);
        }

        $this->config = array_merge($this->config, $config);
        $this->conns = $conns;
    }

    public function __invoke()
    {
        // return function() {

            //callback call
            foreach($this->conns as $conn) {

                try {
                                            
                    if($connInfo = $this->conns->getInfo()) {
                        
                        $connInfo->incrementCount();

                        //response
                        $responseCallback = call_user_func_array($this->config['callback'], array($conn, $connInfo->getRequest(), $connInfo->getData()));

                        if($connInfo->getCounter() >= $this->config['callsLimit'] or $responseCallback === true) {

                            //sent response
                            $buffer = ( $responseCallback === true ? 
                                        $this->config['response']['done'] : 
                                        ( $responseCallback === false ? 
                                            $this->config['response']['wait'] : 
                                            $this->config['response']['error'] ) );
                                                        
                            $conn->write($buffer);
                            $conn->end();
                        } else {
                            $this->conns->attach($conn, $connInfo);
                        }
                    }

                } catch (Exception $e) {
                    if($conn) {
                        $conn->write($this->config['response']['error']);
                        $conn->end(); 
                    }
                }
            }
        // };
    }
}