<?php

require_once 'lib/bootstrap.php';

$schematic = new \Controllers\Schematic();

try
{

    if($schematic->exists())
    {

        $schematic->generate();

    }
    else
    {

        throw new \Exception('No schematics exist...');

    }

}
catch(Exception $e)
{

    echo $e->getMessage();

}