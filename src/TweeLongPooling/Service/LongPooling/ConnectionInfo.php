<?php

namespace TweeLongPooling\Service\LongPooling;

class ConnectionInfo
{
    /**
    * Counter
    * 
    * @var Integer
    */    
    protected $counter  = 0;
    
    /**
    * Connecting time
    * 
    * @var Integer
    */    
    protected $time     = 0;
    
    /**
    * Request params and headers
    * 
    * @var React\Http\Request
    */    
    protected $request  = null;
    
    /**
    * Request data
    * 
    * @var String
    */     
    protected $data     = '';

    /**
    * Constructor
    * 
    * @param React\Http\Request|null $request
    * @param String $data
    */
    public function __construct($request, $data)
    {
        $this->time     = time();
        $this->request  = $request;
        $this->data     = $data;
    }

    /**
    * Increment counter
    */
    public function incrementCounter()
    {
        $this->counter++;
    }    

    /**
    * Decrement counter
    */
    public function decrementCounter()
    {
        $this->counter--;
    }
    
    /**
    * Get counter
    * 
    * @return Integer
    */
    public function getCounter()
    {
        return $this->counter;
    }    

    /**
    * Get connection time
    * 
    * @return Integer
    */
    public function getTime()
    {
        return $this->time;
    }

    /**
    * Get request
    * 
    * @return React\Http\Request
    */
    public function getRequest()
    {
        return $this->request;
    }

    /**
    * Get request data
    * 
    * @return String
    */
    public function getData()
    {
        return $this->data;
    }
}