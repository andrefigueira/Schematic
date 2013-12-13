<?php

require_once 'lib/bootstrap.php';

$schematic = new \Controllers\Schematic();

try
{

    $directory = __DIR__ . '/schemas';

    $dir = new DirectoryIterator($directory);

    foreach($dir as $fileinfo)
    {

        $schematic->tableDir = $fileinfo->getFilename();

        if(!$fileinfo->isDot())
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

    }

}
catch(Exception $e)
{

    echo $e->getMessage();

}