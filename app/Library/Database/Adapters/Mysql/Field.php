<?php

namespace Library\Database\Adapters\Mysql;

use Library\Database\Adapters\Interfaces\AdapterInterface;
use Library\Database\Adapters\Interfaces\FieldInterface;

class Field implements FieldInterface
{

    protected $name;

    protected $properties;

    protected $databaseProperties;

    protected $propertyModifications = array();

    public function __construct(AdapterInterface $adapter)
    {

        $this->adapter = $adapter;

    }

    public function setName($name)
    {

        $this->name = $name;

        return $this;

    }

    public function setProperties($properties)
    {

        $this->properties = $properties;

        return $this;

    }

    public function create()
    {

        $query = $this->adapter->db->exec('
        ');

        if($query)
        {

            return true;

        }
        else
        {

            throw new \Exception('Unable to create field');

        }

    }

    public function exists()
    {

        if(isset($this->properties->rename))
        {

            $this->setName($this->properties->rename);

        }

        $stmt = $this->adapter->db->prepare('
        SHOW COLUMNS
        FROM hello_world
        WHERE Field = :name
        ');

        $stmt->bindParam('name', $this->name);

        if($stmt->execute())
        {

            return (bool) $stmt->rowCount();

        }
        else
        {

            throw new \Exception('Unable to check if field exists');

        }

    }

    protected function fetchProperties()
    {

        $stmt = $this->adapter->db->prepare('
        SHOW COLUMNS
        FROM hello_world
        WHERE Field = :name
        ');

        $stmt->bindParam('name', $this->name);

        if($stmt->execute())
        {

            $properties = new \stdClass();

            while($row = $stmt->fetch())
            {

                $type = explode(' ', $row['Type']);

                $realType = $type[0];

                if(isset($type[1])){ $unsigned = $type[1];}else{ $unsigned = false;}
                if($row['Null'] == 'NO'){ $null = false;}else{ $null = true;}

                $properties->type = $realType;
                $properties->unsigned = $unsigned;
                $properties->null = $null;
                $properties->default = $row['Default'];
                $properties->extra = $row['Extra'];

            }

            return $properties;

        }
        else
        {

            throw new \Exception('Unable to fetch field properties');

        }

    }

    public function modified()
    {

        $modified = false;

        $this->databaseProperties = $this->fetchProperties();

        foreach($this->databaseProperties as $property => $value)
        {

            if(isset($this->properties->{$property}) && $this->properties->{$property} != $value)
            {

                array_push($this->propertyModifications, array(
                    'name' => $property,
                    'value' => $this->properties->{$property}
                ));

                $modified = true;

            }

        }

        return $modified;

    }

    public function update()
    {



    }

    protected function createRelation($field, $relatedTable, $relatedField)
    {


    }

    protected function createIndex($name, $field, $type)
    {



    }

}