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
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'engine' => 'InnoDB'
        ),
        'tables' => array(
            'hello_world' => array(
                'fields' => array(
                    'id' => array(
                        'type' => 'int(11)',
                        'autoIncrement' => true,
                        'index' => 'PRIMARY'
                    ),
                    'banana' => array(
                        'type' => 'varchar(128)',
                        'index' => 'INDEX',
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

    if($database->exists())
    {

        if($database->modified())
        {

            $database->update();

        }

    }
    else
    {

        $database->create();

    }

    $messages = array();

    foreach($schema->database->tables as $table => $fields)
    {

        $table = $database->getTable($table);

        if($table->exists())
        {

        }
        else
        {

            $table->create();

        }

        foreach($fields->fields as $name => $properties)
        {

            $field = $table->getField($name, $properties);

            if($field->exists())
            {

                if($field->modified() === true)
                {

                    $field->update();

                }

            }
            else
            {

                $field->create();

            }

        }

    }

}
catch(Exception $e)
{

    echo $e->getMessage();

}