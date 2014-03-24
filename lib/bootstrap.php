<?php

header('Content-type: text/html; charset=utf-8');

include_once 'config.php';

if(session_id() == ''){ session_start();}

require_once dirname(dirname(__FILE__)) . '/vendor/autoload.php';