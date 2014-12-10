<?php 

namespace TweeLongPooling\Service;

use Exception;
use InvalidArgumentException;

class TimerCallback
{
    private $config = [
                'callsLimit' => 20, 
                'response' => ['done' => 'done', 'wait' => 'wait', 'error' => 'error'] ];

    private $callback;

    public function setConfig(Array $config) 
    {
        if(!array_key_exists('callback', $config) or !is_callable($config['callback'])) {
            throw new InvalidArgumentException('Invalid callback', 201);
        }

        $this->config = array_merge($this->config, $config);
    }

    public function setCallback(Callable $callback) 
    {
        if(!is_callable($callback)) {
            throw new InvalidArgumentException('Invalid time period callback', 202);
        }         
        $this->callback = $callback;
    }

    public function getCallback()
    {
        return $this->callback ? : $this->createCallback();
    }

    private function createCallback()
    {
        return function(&$conns) {

            //callback call
            foreach($conns as $conn) {

                try {
                                            
                    if($connInfo = $conns->getInfo()) {
                        
                        $connInfo->incrementCount();

                        //response
                        $responseCallback = call_user_func_array($this->config['callback'], array($conn, $connInfo->getRequest(), $connInfo->getData()));

                        if($connInfo->getCount() >= $this->config['callsLimit'] or $responseCallback === true) {

                            //sent response
                            $buffer = ( $responseCallback === true ? 
                                        $this->config['response']['done'] : 
                                        ( $responseCallback === false ? 
                                            $this->config['response']['wait'] : 
                                            $this->config['response']['error'] ) );
                                                        
                            $conn->write($buffer);
                            $conn->end();
                        } else {
                            $conns->attach($conn, $connInfo);
                        }
                    }

                } catch (Exception $e) {
                    if($conn) {
                        $conn->write($this->config['response']['error']);
                        $conn->end(); 
                    }
                }
            }
        };
    }
}