<?php

namespace Controllers\Database\Adapters;

use Controllers\Database\DatabaseInterface;

class MysqlAdapter implements DatabaseInterface
{

    protected $db;

    protected $host;

    protected $username;

    protected $password;

    protected $dbName;

    public function connect()
    {

        $this->db = new \mysqli($this->host, $this->username, $this->password);

        $this->db->autocommit(false);

        if($this->db->connect_errno){ throw new \Exception($this->db->connect_error);}

    }

    public function setHost($host)
    {

        $this->host = $host;

        return $this;

    }

    public function setUsername($username)
    {

        $this->username = $username;

        return $this;

    }

    public function setPassword($password)
    {

        $this->password = $password;

        return $this;

    }

    public function setDbName($dbName)
    {

        $this->dbName = $dbName;

        return $this;

    }

    public function createDatabase($name)
    {

        $result = $this->db->query('CREATE DATABASE IF NOT EXISTS `' . $name . '`');

        return $result;

    }

    private function selectDb()
    {

        if($this->dbName != '')
        {

            if($this->db->select_db($this->dbName))
            {

                return true;

            }
            else
            {

                throw new \Exception('Unable to select database: ' . $this->db->error);

            }

        }
        else
        {

            throw new \Exception('Database name is empty...');

        }

    }

    public function tableExists($name)
    {

        $this->selectDb();

        $result = $this->db->query('SHOW TABLES LIKE "' . $name . '"');

        if($result)
        {

            return (bool) $result->num_rows;

        }
        else
        {

            throw new \Exception('Unable to check if table exists: ' . $this->db->error);

        }

    }

    public function fieldExists($table, $field)
    {

        $result = $this->db->query('
        SELECT *
        FROM information_schema.COLUMNS
        WHERE
        TABLE_SCHEMA = "' . $this->dbName . '"
        AND TABLE_NAME = "' . $table . '"
        AND COLUMN_NAME = "' . $field . '"
        ');

        if($result)
        {

            return (bool)$result->num_rows;

        }
        else
        {

            throw new \Exception('Failure in checking if column exists: '. $this->db->error);

        }

    }

    public function query($query)
    {

        $result = $this->db->query($query);

        if($result)
        {

            return true;

        }
        else
        {

            $this->db->rollback();

            throw new \Exception('Failed to run query: ' . $query .' : ' . $this->db->error);

        }

    }

    public function multiQuery($query)
    {

        $result = $this->db->multi_query($query);

        if($result)
        {

            return true;

        }
        else
        {

            $this->db->rollback();

            throw new \Exception('Failed to run multiquery: ' . $this->db->error);

        }

    }

    public function showFields($table)
    {

        $result = $this->db->query('SHOW COLUMNS FROM ' . $table);

        if($result)
        {

            $resultsArray = array();

            while($row = $result->fetch_object())
            {

                array_push($resultsArray, $row->Field);

            }

            return $resultsArray;

        }
        else
        {

            throw new \Exception('Unable to check if table exists: ' . $this->db->error);

        }

    }

    public function commit()
    {

        $this->db->commit();

    }

    public function mapDatabase()
    {

        $this->selectDb();

         return $this->fetchTables();

    }

    private function fetchTables()
    {

        $result = $this->db->query('SHOW tables;');

        if($result)
        {

            $resultsObj = new \stdClass();

            while($row = $result->fetch_object())
            {

                $resultsObj->{$row->Tables_in_promotions} = $this->fetchFields($row->Tables_in_promotions);

            }

            return $resultsObj;

        }
        else
        {

            throw new \Exception('Unable to fetch tables: ' . $this->db->error);

        }

    }

    private function fetchFields($table)
    {

        $result = $this->db->query('DESCRIBE ' . $table . ';');

        if($result)
        {

            $resultsObj = new \stdClass();

            while($row = $result->fetch_object())
            {

                $row->foreignKeys = $this->fetchFieldConstraints($table, $row->Field);

                $resultsObj->{$row->Field} = $row;

            }

            return $resultsObj;

        }
        else
        {

            throw new \Exception('Unable to fetch table fields: ' . $this->db->error);

        }

    }

    private function fetchFieldConstraints($table, $field)
    {

        $query = '
        SELECT *
        FROM information_schema.key_column_usage
        WHERE referenced_table_name IS NOT NULL
        AND TABLE_NAME = "' . $table . '"
        AND COLUMN_NAME = "' . $field . '"
        LIMIT 1
        ';

        $result = $this->db->query($query);

        if($result)
        {

            while($row = $result->fetch_object())
            {

                $row->actions = $this->fetchFieldConstraintsActions($row->CONSTRAINT_NAME);

                return $row;

            }

        }
        else
        {

            throw new \Exception('Unable to fetch field constraints: ' . $this->db->error);

        }

    }

    private function fetchFieldConstraintsActions($constraintName)
    {

        $query = '
        SELECT *
        FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS
        WHERE CONSTRAINT_NAME = "' . $constraintName . '"
        LIMIT 1
        ';

        $result = $this->db->query($query);

        if($result)
        {

            while($row = $result->fetch_object())
            {

                return $row;

            }

        }
        else
        {

            throw new \Exception('Unable to fetch field constraints actions: ' . $this->db->error);

        }

    }

}