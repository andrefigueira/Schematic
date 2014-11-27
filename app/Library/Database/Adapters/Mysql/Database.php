<?php

namespace Library\Database\Adapters\Mysql;

use Library\Database\Adapters\Interfaces\AdapterInterface;
use Library\Database\Adapters\Interfaces\DatabaseInterface;

class Database implements DatabaseInterface
{

    protected $name;

    protected $charset;

    protected $collation;

    protected $engine;

    protected $variables;

    protected $variableModifications = array();

    public function __construct(AdapterInterface $adapter)
    {

        $this->adapter = $adapter;
        $this->variables = $this->fetchVariables();

    }

    public function setName($name)
    {

        $this->name = $name;

        return $this;

    }

    public function setCharset($charset)
    {

        $this->charset = $charset;

        return $this;

    }

    public function setCollation($collation)
    {

        $this->collation = $collation;

        return $this;

    }

    public function setEngine($engine)
    {

        $this->engine = $engine;

        return $this;

    }

    protected function fetchVariables()
    {

        $stmt = $this->adapter->db->prepare('
        SHOW VARIABLES
        WHERE Variable_name IN (
            "character_set_database",
            "collation_database",
            "storage_engine"
        );
        ');

        if($stmt->execute())
        {

            $variables = new \stdClass();

            while($row = $stmt->fetch())
            {

                $variables->{$this->semantisizeVariableName($row['Variable_name'])} = $row['Value'];

            }

            return $variables;

        }
        else
        {

            throw new \Exception('Unable to fetch database variables');

        }

    }

    private function semanticVariableNames($reverse = false)
    {

        $array = array(
            'character_set_database' => 'charset',
            'collation_database' => 'collation',
            'storage_engine' => 'engine'
        );

        if($reverse){ $array = array_flip($array);}

        return $array;

    }

    private function semantisizeVariableName($name, $reverse = false)
    {

        return $this->semanticVariableNames($reverse)[$name];

    }

    public function exists()
    {

        $stmt = $this->adapter->db->prepare('
        SHOW DATABASES
        LIKE :name;
        ');

        $stmt->bindParam('name', $this->name);

        if($stmt->execute())
        {

            return (bool) $stmt->rowCount();

        }
        else
        {

            throw new \Exception('Unable to check if database exists');

        }

    }

    public function modified()
    {

        $modified = false;

        foreach($this->variables as $variable => $value)
        {

            if(isset($this->{$variable}) && $this->{$variable} != $value)
            {

                array_push($this->variableModifications, array(
                    'name' => $variable,
                    'value' => $this->{$variable}
                ));

                $modified = true;

            }

        }

        return $modified;

    }

    public function create()
    {

        $query = $this->adapter->db->exec('
        CREATE DATABASE ' . $this->name . ' CHARACTER SET ' . $this->charset . ' COLLATE ' . $this->collation . ';
        ');

        if($query)
        {

            return true;

        }
        else
        {

            throw new \Exception('Unable to create database');

        }

    }

    public function update()
    {

        foreach($this->variableModifications as $mofification)
        {

            $query = $this->adapter->db->exec('
            SET ' . $this->semantisizeVariableName($mofification['name'], true) . '=' . $mofification['value'] . ';
            ');

            if($query === false)
            {

                throw new \Exception('Unable to update database variables');

            }

        }

    }

    public function getTable($name)
    {

        $table = new Table($this->adapter);

        $table
            ->setName($name)
            ->setDatabaseName($this->name);

        return $table;

    }

}