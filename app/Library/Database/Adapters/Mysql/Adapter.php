<?php

namespace Library\Database\Adapters\Mysql;

use Library\Database\Adapters\Interfaces\AdapterInterface;
use Library\Helpers\SchematicHelper;

/**
 * Class Adapter
 * @package Library\Database\Adapters\Mysql
 */
class Adapter implements AdapterInterface
{

    /**
     * @var string
     */
    public $db;

    /**
     * @var string
     */
    protected $host = '127.0.0.1';

    /**
     * @var string
     */
    protected $user = 'root';

    /**
     * @var string
     */
    protected $pass = '';

    /**
     * @var string
     */
    protected $databaseName;

    public function __construct($databaseName)
    {
        $this->setDatabaseName($databaseName);
    }

    /**
     * @param string $host
     *
     * @return $this
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @param string $pass
     *
     * @return $this
     */
    public function setPass($pass)
    {
        $this->pass = $pass;

        return $this;
    }

    /**
     * @param string $user
     *
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @param $databaseName
     */
    public function setDatabaseName($databaseName)
    {
        $this->databaseName = $databaseName;
    }

    /**
     * @return $this
     */
    public function connect()
    {
        try {
            $this->db = new \PDO('mysql:host=' . $this->host . ';', $this->user, $this->pass);

            $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            return $this;
        } catch (\Exception $e) {
            SchematicHelper::writeln('<error>An error occured: ' . $e->getMessage() . '</error>');
            exit;
        }
    }

    /**
     * @param $databaseName
     * @return bool
     * @throws Exception
     */
    public function useDatabase($databaseName)
    {
        $sql = 'use ' . $databaseName;

        $statement = $this->db->prepare($sql);

        if ($statement->execute()) {
            return true;
        } else {
            throw new Exception('Unable to select database');
        }
    }

    /**
     * @return mixed
     */
    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    public function fetchTables()
    {
        $sql = '
        SELECT table_name as tables
        FROM information_schema.tables
        WHERE table_schema = DATABASE()
        AND table_type != "VIEW"
        ';

        $statement = $this->db->prepare($sql);

        if ($statement->execute()) {
            $resultsObj = new \stdClass();

            while ($row = $statement->fetch(\PDO::FETCH_OBJ)) {
                $resultsObj->{$row->tables} = $this->fetchFields($row->tables);
            }

            return $resultsObj;
        } else {
            throw new Exception('Unable to fetch tables');
        }
    }

    /**
     * @return \stdClass
     * @throws \Exception
     */
    public function fetchDatabaseVariables()
    {
        $sql = 'SHOW variables;';

        $statement = $this->db->prepare($sql);

        if ($statement->execute()) {

            $resultsObj = new \stdClass();

            while($row = $statement->fetch(\PDO::FETCH_OBJ))
            {
                $resultsObj->{$row->Variable_name} = $row->Value;
            }

            return $resultsObj;
        } else {
            throw new \Exception('Unable to fetch variables');
        }
    }

    /**
     * @param $table
     * @return \stdClass
     * @throws \Exception
     */
    public function fetchFields($table)
    {
        $sql = 'DESCRIBE ' . $table;

        $statement = $this->db->prepare($sql);

        if ($statement->execute()) {

            $resultsObj = new \stdClass();

            while($row = $statement->fetch(\PDO::FETCH_OBJ))
            {
                $row->foreignKeys = $this->fetchFieldConstraints($table, $row->Field);
                $resultsObj->{$row->Field} = $row;
            }
            return $resultsObj;
        } else {
            throw new \Exception('Unable to fetch fields');
        }
    }

    /**
     * @param $table
     * @param $field
     * @return mixed
     * @throws \Exception
     */
    public function fetchFieldConstraints($table, $field)
    {
        $sql = '
        SELECT *
        FROM information_schema.key_column_usage
        WHERE referenced_table_name IS NOT NULL
        AND TABLE_NAME = "' . $table . '"
        AND COLUMN_NAME = "' . $field . '"
        LIMIT 1
        ';

        $statement = $this->db->prepare($sql);

        if ($statement->execute()) {

            while($row = $statement->fetch(\PDO::FETCH_OBJ))
            {
                $row->actions = $this->fetchFieldConstraintsActions($row->CONSTRAINT_NAME);

                return $row;
            }
        } else {
            throw new \Exception('Unable to fetch fields constraints');
        }
    }

    /**
     * @param $constraintName
     * @return mixed
     * @throws \Exception
     */
    public function fetchFieldConstraintsActions($constraintName)
    {
        $sql = '
        SELECT *
        FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS
        WHERE CONSTRAINT_NAME = "' . $constraintName . '"
        LIMIT 1
        ';

        $statement = $this->db->prepare($sql);

        if ($statement->execute()) {
            while($row = $statement->fetch(\PDO::FETCH_OBJ))
            {
                return $row;
            }
        } else {
            throw new \Exception('Unable to fetch fields constraint actions');
        }
    }

    /**
     * @return \stdClass
     * @throws Exception
     */
    public function mapDatabase()
    {
        return $this->fetchTables();
    }
}
