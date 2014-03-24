<?php

//Versions
define('VERSION', '1.2.6');

//Debug and Live

define('ENV_DIR', dirname(__DIR__) . '/');

$envFile = ENV_DIR . 'env.conf';

if(file_exists($envFile))
{

	$value = file_get_contents($envFile);
	
	if($value)
	{
	
		define('ENVIRONMENT', $value);

	}
	else
	{
		
		die('Unable to get env.conf value');
		
	}

}
else
{

	die('No environment file found...');

}