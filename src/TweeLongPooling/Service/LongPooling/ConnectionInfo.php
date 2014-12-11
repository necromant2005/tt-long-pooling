<?php

namespace TweeLongPooling\Service\LongPooling;

use BadMethodCallException;

class ConnectionInfo
{
    protected $counter  = 0;
    protected $time     = 0;
    protected $request  = null;
    protected $data     = '';

    public function __construct($request, $data)
    {
        $this->time     = time();
        $this->request  = $request;
        $this->data     = $data;
    }

    public function incrementCounter()
    {
        $this->counter++;
    }    

    public function decrementCounter()
    {
        $this->counter--;
    }
    
    public function getCounter()
    {
        return $this->counter;
    }    

    public function getTime()
    {
        return $this->time;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getData()
    {
        return $this->data;
    }
}