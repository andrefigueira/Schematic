<?php

//Versions
define('SITE_VERSION', '1.0');

//Debug and Live
define('DEBUG', true);
define('MINIFY', false);
define('REDIS_SESSIONS', false);

define('INCLUDE_DIR', dirname(__DIR__) . '/app/includes');
define('ENV_DIR', dirname(__DIR__) . '/');

if(file_exists(ENV_DIR . '.env'))
{

	$value = file_get_contents(ENV_DIR . '.env');
	
	if($value)
	{
	
		define('ENVIRONMENT', $value);

	}
	else
	{
		
		die('Unabled to get .env value');
		
	}

}
else
{

	die('No environment file found...');

}

//Database details
switch(trim(ENVIRONMENT))
{

	case 'localhost':	
		define('BASE_URL', '');
		define('DB_HOST', 'localhost');
		define('DB_USER', 'root');
		define('DB_PASS', 'root');
		define('DB_NAME', '');
	break;
		
	case 'live':
		define('BASE_URL', '');
		define('DB_HOST', 'localhost');
		define('DB_USER', 'root');
		define('DB_PASS', 'root');
		define('DB_NAME', '');
	break;
		
	default:
		die('Invalid environment set: ' . ENVIRONMENT);
	
}

define('FRONTEND_DIR', BASE_URL . 'frontend/');

if(DEBUG)
{
	
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	
}
else
{

	error_reporting(0);
	ini_set('display_errors', 0);
	
}

//S3 CDN Info
define('CDN_URL', '');

//Email settings
define('DEFAULT_FROM_EMAIL', 'welcome@email.com');
define('HTML_EMAIL_TEMPLATE', 'html-email/template.html');

//Facebook API details
define('FB_APP_ID', '');
define('FB_APP_SECRET', '');

//Twitter API details
define('TW_APP_ID', '');
define('TW_APP_SECRET', '');

//Vimeo API details
define('VIMEO_CLIENT_ID', '');
define('VIMEO_CLIENT_SECRET', '');

//Youtube API details
define('YOUTUBE_CALL_API', 'https://gdata.youtube.com/feeds/api/videos?v=2&alt=jsonc&format=5&max-results=48&q=');