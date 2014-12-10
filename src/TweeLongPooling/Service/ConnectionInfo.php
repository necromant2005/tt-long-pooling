<?php

namespace TweeLongPooling\Service;

use BadMethodCallException;

class ConnectionInfo
{
    protected $count = 0;
    protected $time;
    protected $request;
    protected $data;

    public function __construct($request, $data)
    {
        $this->count = 0;
        $this->time = time();
        $this->request = $request;
        $this->data = $data;
    }

    public function incrementCount()
    {
        $this->count++;
    }    

    public function decrementCount()
    {
        $this->count--;
    }
    
    public function __call($fname, $args)
    {
        $name = strtolower(str_replace('get', '', $fname));

        if(property_exists(__CLASS__, $name)) {
            return $this->$name;
        } else {
            throw new BadMethodCallException('Bad method call', 201);
        }
    }
}