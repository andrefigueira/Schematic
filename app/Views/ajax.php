<?php

/**
 * This file is the handler for ajax requests, it calls the methods of the Ajax class which route to the data models
 */

$general = new \Core\General();

$controller = ucfirst($general->getVar('controller', 'get'));
$method = $general->getVar('method', 'get');

$class = '\\Controllers\\' . $controller;

$inst = new $class();

if(method_exists($inst, $method))
{

    call_user_func(array($inst, $method), $inst);

}
else
{

    throw new Exception('Invalid request, method does not exist');

}