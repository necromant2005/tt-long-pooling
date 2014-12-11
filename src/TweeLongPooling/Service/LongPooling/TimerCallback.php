<?php 

namespace TweeLongPooling\Service\LongPooling;

use Exception;
use InvalidArgumentException;
use SplObjectStorage;

class TimerCallback
{
    CONST OPT_CALLS_LIMIT       = 'callsLimit';
    CONST OPT_CALLBACK          = 'callback';
    CONST OPT_RESPONSE          = 'response';
    CONST OPT_RESPONSE_DONE     = 'done';
    CONST OPT_RESPONSE_WAIT     = 'wait';
    CONST OPT_RESPONSE_ERROR    = 'error';

    /**
    *
    * Default options
    *
    * @var Array
    */
    private $options = [
                self::OPT_CALLS_LIMIT => 20, 
                self::OPT_RESPONSE => [
                    self::OPT_RESPONSE_DONE   => 'done', 
                    self::OPT_RESPONSE_WAIT   => 'wait', 
                    self::OPT_RESPONSE_ERROR  => 'error'] ];

    /**
    *
    * Pull of the connections
    *
    * @var SplObjectStorage
    */
    private $conns;

    public function __construct(Array $options, SplObjectStorage $conns) 
    {
        if(!($clbExists = array_key_exists(self::OPT_CALLBACK, $options)) or !is_callable($options[self::OPT_CALLBACK])) {
            throw new InvalidArgumentException('Invalid callback', ($clbExists ? 202 : 201));
        }

        $this->options = array_merge($this->options, $options);
        $this->conns = $conns;
    }

    public function __invoke()
    {
        foreach($this->conns as $conn) {

            try {

                if($connInfo = $this->conns->getInfo()) {
                    
                    $connInfo->incrementCounter();

                    //response
                    $responseCallback = call_user_func_array($this->options[self::OPT_CALLBACK], array($conn, $connInfo->getRequest(), $connInfo->getData()));

                    if($connInfo->getCounter() >= $this->options[self::OPT_CALLS_LIMIT] or $responseCallback === true) {

                        //sent response
                        $buffer = ( $responseCallback === true ? 
                                    $this->options[self::OPT_RESPONSE][self::OPT_RESPONSE_DONE] : 
                                    ( $responseCallback === false ? 
                                        $this->options[self::OPT_RESPONSE][self::OPT_RESPONSE_WAIT] : 
                                        $this->options[self::OPT_RESPONSE][self::OPT_RESPONSE_ERROR] ) );

                        $conn->write($buffer);
                        $conn->end();
                    } else {
                        $this->conns->attach($conn, $connInfo);
                    }
                }

            } catch (Exception $e) {
                if($conn) {
                    $conn->write($this->options[self::OPT_RESPONSE][self::OPT_RESPONSE_ERROR]);
                    $conn->end(); 
                }
            }
        }
    }
}