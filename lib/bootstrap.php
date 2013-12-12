<?php

header('Content-type: text/html; charset=utf-8');

include_once 'config.php';

if(REDIS_SESSIONS)
{

    //Use REDIS for sessions
    ini_set('session.save_handler', 'redis');
    ini_set('session.save_path', 'tcp://127.0.0.1:6379');
    
}

if(session_id() == ''){ session_start();}

require_once dirname(dirname(__FILE__)) . '/vendor/autoload.php';

use Core\General;

$general = new General();

//Run the output buffer
$general->ob();