tt-long-pooling
===============

[![Build Status](https://travis-ci.org/necromant2005/tt-long-pooling.svg?branch=master)](https://travis-ci.org/necromant2005/tt-long-pooling)

Introduction
------------

PHP long pooling

Installation
------------

### Main Setup

#### With composer

1. Add this to your composer.json:

```json
"require": {
    "necromant2005/tt-long-pooling": "1.*",
}
```

#### Usage

```php
use TweeLongPooling\Service\LongPooling;

$options =  [
    'callsLimit' => $callsLimit,
    'callback' => $callback,
    'response' => [
        'done'  => $done,
        'wait'  => $wait,
        'error' => $error, 
    ],
]; 
   
(new LongPooling($options, $listen, $timePeriod))->run();
```
 * $callsLimit - system iterations count;
 * $callback - function to call in each iteration;
 * $done - responce on done;
 * $wait - responce on wait;
 * $error - responce on error;
 * $listen - array of listened ports;
 * $timePeriod - iteration time period;

$callback function will be called $callsLimit times. If $callback returns 'true' then responce with $done as body will be returned . If $callback returns 'false' and there is no $callsLimit to execute then response with $wait as body will be returned.  If callback returns nor 'true' nor 'false' then response with $error as body will be returned .
