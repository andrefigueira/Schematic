<?php

namespace Library\Database\Adapters\Mysql;

use Library\Database\Adapters\Interfaces\AdapterInterface;
use Library\Database\Adapters\Interfaces\FieldInterface;

class Field implements FieldInterface
{
    protected $name;

    protected $properties;

    protected $databaseProperties;

    protected $databaseName;

    protected $tableName;

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

    public function setDatabaseName($databaseName)
    {
        $this->databaseName = $databaseName;

        return $this;
    }

    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    private function prepareProperties()
    {
        foreach ($this->properties as $key => $value) {
            switch ($key) {

                case 'autoIncrement':
                    if ($value === true) {
                        $this->properties->{$key} = 'AUTO_INCREMENT';
                    }
                    break;

                case 'unsigned':
                    if ($value === true) {
                        $this->properties->{$key} = 'unsigned';
                    }
                    break;

                default:

            }
        }
    }

    public function create()
    {
        $this->prepareProperties();

        $query = $this->adapter->db->query('
        ALTER TABLE '.$this->tableName.' ADD '.$this->name.' '.$this->properties->type.' '.(isset($this->properties->autoIncrement) ? $this->properties->autoIncrement : '').';
        ');

        if ($query) {
            return true;
        } else {
            throw new \Exception('Unable to create field');
        }
    }

    public function exists()
    {
        $stmt = $this->adapter->db->prepare('
        SHOW COLUMNS
        FROM '.$this->tableName.'
        WHERE Field = :name
        ');

        $stmt->bindParam(':name', $this->name);

        if ($stmt->execute()) {
            return (bool) $stmt->rowCount();
        } else {
            throw new \Exception('Unable to check if field exists');
        }
    }

    protected function fetchProperties()
    {
        $stmt = $this->adapter->db->prepare('
        SHOW COLUMNS
        FROM '.$this->tableName.'
        WHERE Field = :name
        ');

        $stmt->bindParam('name', $this->name);

        if ($stmt->execute()) {
            $properties = new \stdClass();

            while ($row = $stmt->fetch()) {
                $type = explode(' ', $row['Type']);

                $realType = $type[0];

                if (isset($type[1])) {
                    $unsigned = $type[1];
                } else {
                    $unsigned = false;
                }
                if ($row['Null'] == 'NO') {
                    $null = false;
                } else {
                    $null = true;
                }

                $properties->type = $realType;
                $properties->unsigned = $unsigned;
                $properties->null = $null;
                $properties->default = $row['Default'];
                $properties->extra = $row['Extra'];
            }

            return $properties;
        } else {
            throw new \Exception('Unable to fetch field properties');
        }
    }

    public function modified()
    {
        $modified = false;

        $this->databaseProperties = $this->fetchProperties();

        foreach ($this->databaseProperties as $property => $value) {
            if (isset($this->properties->{$property}) && $this->properties->{$property} != $value) {
                array_push($this->propertyModifications, array(
                    'name' => $property,
                    'value' => $this->properties->{$property},
                ));

                $modified = true;
            }
        }

        if (isset($this->properties->rename)) {
            $modified = true;
        }

        if (isset($this->properties->index) && $this->indexExists() === false) {
            $modified = true;
        }

        return $modified;
    }

    public function update()
    {
        $this->prepareProperties();

        if (isset($this->properties->rename)) {
            $existingName = $this->name;

            $this->setName($this->properties->rename);

            if ($this->exists() === true) {
                throw new \Exception('Column already exists, cannot rename...');
            }

            $this->setName($existingName);

            $sql = 'ALTER TABLE '.$this->tableName.' CHANGE COLUMN `'.$this->name.'` `'.$this->properties->rename.'` '.$this->properties->type.' '.(isset($this->properties->autoIncrement) ? $this->properties->autoIncrement : '').';';
        } else {
            if (isset($this->properties->index) && $this->indexExists() === false) {
                $this->createIndex($this->properties->index);
            }

            $sql = 'ALTER TABLE '.$this->tableName.' MODIFY COLUMN '.$this->name.' '.$this->properties->type.' '.(isset($this->properties->autoIncrement) ? $this->properties->autoIncrement : '').';';
        }

        $query = $this->adapter->db->query($sql);

        if ($query) {
            if (isset($this->properties->index) && $this->indexExists() === false) {
                $this->createIndex($this->properties->index);
            }

            return true;
        } else {
            throw new \Exception('Unable to create field');
        }
    }

    protected function validIndexTypes()
    {
        return array(
            'INDEX',
            'UNIQUE',
            'PRIMARY KEY',
        );
    }

    protected function isValidIndex($type)
    {
        return in_array($type, $this->validIndexTypes());
    }

    protected function indexExists()
    {
        $stmt = $this->adapter->db->prepare('
        SHOW INDEXES
        FROM '.$this->tableName.'
        WHERE Column_name = :name
        ');

        $stmt->bindParam(':name', $this->name);

        if ($stmt->execute()) {
            return (bool) $stmt->rowCount();
        } else {
            throw new \Exception('Unable to check if index exists');
        }
    }

    protected function createIndex($type)
    {
        if (!$this->isValidIndex($type)) {
            throw new \Exception('Invalid index type');
        }
        if ($this->indexExists()) {
            throw new \Exception('Unable to create index, one already exists');
        }

        $query = $this->adapter->db->query('
        ALTER TABLE '.$this->tableName.' ADD '.$type.' '.$this->name.' ('.$this->name.');
        ');

        if ($query) {
            return true;
        } else {
            throw new \Exception('Unable to create index');
        }
    }

    protected function relationExists()
    {
    }

    protected function createRelation($relatedTable, $relatedField)
    {
    }
}
