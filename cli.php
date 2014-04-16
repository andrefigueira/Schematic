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
        ),
        'template' => array(
            'description' => 'Creates a template schema.json file in the schema directory based on the default template',
            'aliases' => array('t')
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
        ),
        array(
            '-t',
            'Create a template schema.json file with the name you pass into the schema folder'
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
elseif(isset($args['template']))
{

    $newFileName = $args['template'] . '.json';
    $newFilePath = './schemas/' . $newFileName;

    if(file_exists($newFilePath))
    {

        cli\line('%k%1' . $newFileName . ' already exists! Cannot overwrite an existing schema file%n');

    }
    else
    {

        $content = @file_get_contents('./templates/schema.json');

        $newTemplate = @file_put_contents($newFilePath, $content);

        cli\line('%b' . 'Created new schema file in schemas folder called: ' . $newFileName . '%n' . PHP_EOL);

    }
}
else
{

    cli\line('%k%1Please enter a recognized command, use -h to see the options available%n');

}