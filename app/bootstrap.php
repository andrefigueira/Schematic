<?php
/**
 * This bootstrap file handles the including of the autoloader and sets up some constants for the application
 */

date_default_timezone_set('UTC');

define('APP_NAME', 'Schematic');
define('APP_ASCI', '
   _____      __                         __  _
  / ___/_____/ /_  ___  ____ ___  ____ _/ /_(______
  \__ \/ ___/ __ \/ _ \/ __ `__ \/ __ `/ __/ / ___/
 ___/ / /__/ / / /  __/ / / / / / /_/ / /_/ / /__
/____/\___/_/ /_/\___/_/ /_/ /_/\__,_/\__/_/\___/
');
define('APP_TITLE', APP_ASCI . APP_NAME);
define('APP_VERSION', '1.5.0');

try
{

    //Setup the autoloader dir
    $autoloadFile = dirname(__DIR__) . '/vendor/autoload.php';

    //Check if exists like this, it will be in this path if standalone
    if(!file_exists($autoloadFile))
    {

        //Not installed standalone, this is a vendor, check above
        $autoloadFile = dirname(dirname(dirname(__DIR__))) . '/autoload.php';

    }

    //Finally check again and include or throw error
    if(file_exists($autoloadFile))
    {

        require_once $autoloadFile;

    }
    else
    {

        throw new Exception('Cannot find autoloader... Please run composer install');

    }

}
catch(\Exception $e)
{

    echo $e->getMessage();

}