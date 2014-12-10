<?php

namespace TweeLongPooling\Service\LongPooling;

use BadMethodCallException;

class ConnectionInfo
{
    protected $counter = 0;
    protected $time;
    protected $request;
    protected $data;

    public function __construct($request, $data)
    {
        $this->counter  = 0;
        $this->time     = time();
        $this->request  = $request;
        $this->data     = $data;
    }

    public function incrementcounterer()
    {
        $this->counter++;
    }    

    public function decrementcounterer()
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