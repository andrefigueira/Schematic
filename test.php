<?php

require_once __DIR__ . '/app/bootstrap.php';


$schema = array(
    'schematic' => array(
        'name' => 'Schematic',
        'version' => '1.5.0'
    ),
    'database' => array(
        'general' => array(
            'name' => 'refactor_testing_db',
            'charset' => 'utf16',
            'collation' => 'utf16_general_ci',
            'engine' => 'MyISAM'
        ),
        'tables' => array(
            'hello_world' => array(
                'fields' => array(
                    'id' => array(
                        'type' => 'int(11)',
                        'auto_increment' => true,
                        'index' => 'PRIMARY'
                    ),
                    'name' => array(
                        'type' => 'int(11)'
                    )
                )
            )
        )
    )
);

$schema = json_decode(json_encode($schema));

$adapter = new \Library\Database\Adapters\Mysql\Adapter($schema->database->general->name);

$database = new \Library\Database\Adapters\Mysql\Database($adapter);
$database
    ->setName($schema->database->general->name)
    ->setCharset($schema->database->general->charset)
    ->setCollation($schema->database->general->collation)
    ->setEngine($schema->database->general->engine);

try
{

    echo 'starting' . PHP_EOL;

    if($database->exists())
    {

        echo 'db exists' . PHP_EOL;

        if($database->modified())
        {

            echo 'db modified' . PHP_EOL;

            $database->update();

        }

    }
    else
    {

        echo 'db does not exist' . PHP_EOL;

        $database->create();

    }

    $messages = array();

    echo 'checking tables' . PHP_EOL;

    foreach($schema->database->tables as $table => $fields)
    {

        echo 'processing table ' . $table . PHP_EOL;

        $table = $database->getTable($table);

        if($table->exists())
        {

            echo 'table exists' . PHP_EOL;

        }
        else
        {

            echo 'table does not exist' . PHP_EOL;

            $table->create();

        }

        echo 'processing fields' . PHP_EOL;

        foreach($fields->fields as $name => $properties)
        {

            echo 'processing field ' . $name . PHP_EOL;

            $field = $table->getField($name, $properties);

            if($field->exists())
            {

                echo 'field exists' . PHP_EOL;

                if($field->modified() === true)
                {

                    echo 'field modified' . PHP_EOL;

                    $field->update();

                }

            }
            else
            {

                echo 'field does not exist' . PHP_EOL;

                $field->create();

            }

        }

    }

}
catch(Exception $e)
{

    echo $e->getMessage() . PHP_EOL;

}