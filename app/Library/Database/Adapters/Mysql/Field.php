<?php

namespace Library\Database\Adapters\Mysql;

use Library\Database\Adapters\Interfaces\AdapterInterface;
use Library\Database\Adapters\Interfaces\FieldInterface;

/**
 * Class Field
 * @package Library\Database\Adapters\Mysql
 */
class Field implements FieldInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $properties;

    /**
     * @var string
     */
    protected $databaseProperties;

    /**
     * @var string
     */
    protected $databaseName;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var array
     */
    protected $propertyModifications = array();

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $properties
     * @return $this
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @return string
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param $databaseName
     * @return $this
     */
    public function setDatabaseName($databaseName)
    {
        $this->databaseName = $databaseName;

        return $this;
    }

    /**
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    /**
     * @param $tableName
     * @return $this
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @return void
     */
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

    /**
     * @return bool
     * @throws \Exception
     */
    public function create()
    {
        $this->prepareProperties();

        $query = $this->adapter->db->query('
        ALTER TABLE `' . $this->tableName . '` ADD `' . $this->name . '` ' . $this->properties->type . ' ' . (isset($this->properties->autoIncrement) ? $this->properties->autoIncrement : '') . ';
        ');

        if ($query) {
            return true;
        } else {
            throw new \Exception('Unable to create field');
        }
    }

    /**
     * @return bool
     * @throws \Exception
     */
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

    /**
     * @return \stdClass
     * @throws \Exception
     */
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

    /**
     * @return bool
     * @throws \Exception
     */
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

    /**
     * @return bool
     * @throws \Exception
     */
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

            $sql = 'ALTER TABLE `' . $this->tableName . '` CHANGE COLUMN `' . $this->name . '` `' . $this->properties->rename . '` ' . $this->properties->type . ' ' . (isset($this->properties->autoIncrement) ? $this->properties->autoIncrement : '') . ';';
        } else {
            if (isset($this->properties->index) && $this->indexExists() === false) {
                $this->createIndex($this->properties->index);
            }

            $sql = 'ALTER TABLE `' . $this->tableName . '` MODIFY COLUMN `' . $this->name . '` ' . $this->properties->type . ' ' . (isset($this->properties->autoIncrement) ? $this->properties->autoIncrement : '') . ';';
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

    /**
     * @return array
     */
    protected function validIndexTypes()
    {
        return array(
            'INDEX',
            'UNIQUE',
            'PRIMARY KEY',
        );
    }

    /**
     * @param $type
     * @return bool
     */
    protected function isValidIndex($type)
    {
        return in_array($type, $this->validIndexTypes());
    }

    /**
     * @return bool
     * @throws \Exception
     */
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

    /**
     * @param $type
     * @return bool
     * @throws \Exception
     */
    protected function createIndex($type)
    {
        if (!$this->isValidIndex($type)) {
            throw new \Exception('Invalid index type');
        }
        if ($this->indexExists()) {
            throw new \Exception('Unable to create index, one already exists');
        }

        $sql = '
        ALTER TABLE `' . $this->tableName . '` ADD ' . $type . ' `' . $this->name . '` (`' . $this->name . '`);
        ';

        $query = $this->adapter->db->query($sql);

        if ($query) {
            return true;
        } else {
            throw new \Exception('Unable to create index');
        }
    }

    public function relationSettingExists()
    {
        return isset($this->getProperties()->foreignKey);
    }

    public function relationExists()
    {
        $constraintSettings = $this->getProperties()->foreignKey;

        $sql = '
        SELECT *
        FROM information_schema.TABLE_CONSTRAINTS
        LEFT JOIN information_schema.KEY_COLUMN_USAGE
        ON information_schema.KEY_COLUMN_USAGE.CONSTRAINT_NAME = information_schema.TABLE_CONSTRAINTS.CONSTRAINT_NAME
        WHERE information_schema.TABLE_CONSTRAINTS.CONSTRAINT_TYPE = "FOREIGN KEY"
        AND information_schema.TABLE_CONSTRAINTS.CONSTRAINT_SCHEMA = "' . $this->getDatabaseName() . '"
        AND information_schema.KEY_COLUMN_USAGE.CONSTRAINT_SCHEMA = "' . $this->getDatabaseName() . '"
        AND information_schema.TABLE_CONSTRAINTS.TABLE_NAME = "' . $this->getTableName() . '"
        AND information_schema.KEY_COLUMN_USAGE.REFERENCED_TABLE_NAME = "' . $constraintSettings->table . '"
        AND information_schema.KEY_COLUMN_USAGE.REFERENCED_COLUMN_NAME = "' . $constraintSettings->field . '"
        ';

        $statement = $this->adapter->db->prepare($sql);

        if ($statement->execute()) {
            return (bool) $statement->rowCount();
        } else {
            throw new \Exception('Unable to check for foreign keys');
        }
    }

    public function createRelation()
    {
        $constraintSettings = $this->getProperties()->foreignKey;

        $sql = '
        ALTER TABLE `' . $this->getTableName() . '` add CONSTRAINT FOREIGN KEY (`' . $this->name . '`)
        REFERENCES `' . $constraintSettings->table . '` (`' . $constraintSettings->field . '`) ON UPDATE ' . $constraintSettings->on->update . ' ON DELETE ' . $constraintSettings->on->delete . ';
        ';

        $query = $this->adapter->db->query($sql);

        return $query;
    }
}
