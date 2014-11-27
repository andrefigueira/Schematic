<?php

namespace Library\Database\Adapters\Mysql;

use Library\Database\Adapters\Interfaces\AdapterInterface;
use Library\Database\Adapters\Interfaces\TableInterface;

class Table implements TableInterface
{

    protected $name;

    protected $databaseName;

    public function __construct(AdapterInterface $adapter)
    {

        $this->adapter = $adapter;

    }

    public function setName($name)
    {

        $this->name = $name;

        return $this;

    }

    public function setDatabaseName($databaseName)
    {

        $this->databaseName = $databaseName;

        return $this;

    }

    public function exists()
    {

        $stmt = $this->adapter->db->prepare('
        SHOW TABLES
        LIKE :name;
        ');

        $stmt->bindParam('name', $this->name);

        if($stmt->execute())
        {

            return (bool) $stmt->rowCount();

        }
        else
        {

            throw new \Exception('Unable to check if table exists');

        }

    }

    public function create()
    {

        $query = $this->adapter->db->exec('
        CREATE TABLE ' . $this->name . ' (
            `id` int(11) unsigned NOT NULL
        );
        ');

        if($query !== false)
        {

            return true;

        }
        else
        {

            throw new \Exception('Unable to create table');

        }

    }

    public function getField($name, $properties)
    {

        $field = new Field($this->adapter);
        $field
            ->setDatabaseName($this->databaseName)
            ->setTableName($this->name)
            ->setName($name)
            ->setProperties($properties);

        return $field;

    }

}