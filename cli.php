#!/usr/bin/php
<?php

require_once 'lib/bootstrap.php';

$args = new cli\Arguments(array(
    'flags' => array(
        'h' => array(
            'description' => 'Help menu',
            'stackable' => true
        ),
        'v' => array(
            'description' => 'Get the software version',
            'stackable' => true
        )
    ),
    'options' => array(
        'run' => array(
            'description' => 'Runs the script and updates the database based on the schema files',
            'aliases' => array('r')
        )
    ),
    'strict' => false
));

try {

    $args->parse();

}
catch(cli\InvalidArguments $e)
{

    echo $e->getMessage() . PHP_EOL;

}

$args = $args->getArguments();

if(isset($args['h']))
{

    $headers = array('Options', 'Description');
    $data = array(
        array(
            '-h',
            'Help menu'
        ),
        array(
            '-r',
            'Runs the script and updates the database based on the schema files'
        ),
        array(
            '-v',
            'Get the version of MySQL Schematic running'
        )
    );

    $table = new cli\Table();
    $table->setHeaders($headers);
    $table->setRows($data);
    $table->display();

}
elseif(isset($args['run']))
{

    try
    {

        $schematic = new \Controllers\Schematic();

        $directory = __DIR__ . '/schemas';

        $dir = new DirectoryIterator($directory);

        foreach($dir as $fileInfo)
        {

            $schematic->schemaFile = $fileInfo->getFilename();

            if(!$fileInfo->isDot())
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

}
elseif(isset($args['v']))
{

    cli\line('%b' . SOFTWARE_NAME . ' - Version: ' . VERSION . '%n' . PHP_EOL);

}
else
{

    cli\line('%k%1Please enter a recognized command, use -h to see the options available%n');

}