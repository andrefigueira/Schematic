<?php

namespace Library\Database\Adapters;

use Library\Database\AbstractDatabaseAdapter;
use Library\Database\DatabaseInterface;

/**
 * Class MysqlAdapter
 *
 * This MySQL adapter implements methods defined on the DatabaseInterface and allows the manipulation of the database
 * in a way which the Schematic tool needs, when trying to determine all parts of the database for imports and
 * for the migrations which are performed on it.
 *
 * @package Library\Database\Adapters
 * @author Andre Figueira <andre.figueira@me.com>
 */
class MysqlAdapter extends AbstractDatabaseAdapter implements DatabaseInterface
{
	/**
	 * @var \mysqli
	 */
    protected $db;

	/**
	 * Creates the DB connection
	 */
    public function connect()
    {
        mysqli_report(MYSQLI_REPORT_STRICT);

        try {
            $this->db = new \mysqli($this->host, $this->username, $this->password);

            $this->db->autocommit(false);

            if ($this->db->connect_errno) {
                throw new \Exception($this->db->connect_error);
            }
        } catch (\Exception $e) {
            echo '---------------------'.PHP_EOL;
            echo $e->getMessage().PHP_EOL;
            echo '---------------------'.PHP_EOL;
            echo 'Terminating migrations...'.PHP_EOL;

            exit;
        }
    }

	/**
	 * @param $name
	 * @return bool|\mysqli_result
	 * @throws \Exception
	 */
    public function createDatabase($name)
    {
        if ($name == '') {
            throw new \Exception('Database name cannot be empty');
        } else {
            $result = $this->db->query('CREATE DATABASE IF NOT EXISTS `'.$name.'`');

            return $result;
        }
    }

	/**
	 * @return bool
	 * @throws \Exception
	 */
    protected function selectDb()
    {
        if ($this->dbName != '') {
            if ($this->db->select_db($this->dbName)) {
                return true;
            } else {
                throw new \Exception('Unable to select database: '.$this->db->error);
            }
        } else {
            throw new \Exception('Database name is empty...');
        }
    }

	/**
	 * @param $name
	 * @return bool
	 * @throws \Exception
	 */
    public function tableExists($name)
    {
        $this->selectDb();

        $result = $this->db->query('SHOW TABLES LIKE "'.$name.'"');

        if ($result) {
            return (bool) $result->num_rows;
        } else {
            throw new \Exception('Unable to check if table exists: '.$this->db->error);
        }
    }

	/**
	 * @param $table
	 * @param $field
	 * @return bool
	 * @throws \Exception
	 */
    public function fieldExists($table, $field)
    {
        $result = $this->db->query('
        SELECT *
        FROM information_schema.COLUMNS
        WHERE
        TABLE_SCHEMA = "'.$this->dbName.'"
        AND TABLE_NAME = "'.$table.'"
        AND COLUMN_NAME = "'.$field.'"
        LIMIT 1
        ');

        if ($result) {
            return (bool) $result->num_rows;
        } else {
            throw new \Exception('Failure in checking if column exists: '.$this->db->error);
        }
    }

	/**
	 * @param $query
	 * @return bool
	 * @throws \Exception
	 */
    public function query($query)
    {
        $result = $this->db->query($query);

        if ($result) {
            return true;
        } else {
            $this->db->rollback();

            throw new \Exception('Failed to run query: '.$query.' : '.$this->db->error);
        }
    }

	/**
	 * @param $query
	 * @return bool
	 * @throws \Exception
	 */
    public function multiQuery($query)
    {
        $result = $this->db->multi_query($query);

        if ($result) {
            return true;
        } else {
            $dbError = $this->db->error;

            $this->db->rollback();

            throw new \Exception('Failed to run multiquery: '.$dbError);
        }
    }

	/**
	 * @param $table
	 * @return array
	 * @throws \Exception
	 */
    public function showFields($table)
    {
        $result = $this->db->query('SHOW COLUMNS FROM '.$table);

        if ($result) {
            $resultsArray = array();

            while ($row = $result->fetch_object()) {
                array_push($resultsArray, $row->Field);
            }

            return $resultsArray;
        } else {
            throw new \Exception('Unable to check if table exists: '.$this->db->error);
        }
    }

	/**
	 * Does a db commit
	 *
	 * @return bool
	 */
    public function commit()
    {
        return $this->db->commit();
    }

	/**
	 * @return \stdClass
	 * @throws \Exception
	 */
    public function mapDatabase()
    {
        $this->selectDb();

        return $this->fetchTables();
    }

	/**
	 * @return \stdClass
	 * @throws \Exception
	 */
    public function fetchDatabaseVariables()
    {
        $result = $this->db->query('SHOW variables;');

        if ($result) {
            $resultsObj = new \stdClass();

            while ($row = $result->fetch_object()) {
                $resultsObj->{$row->Variable_name} = $row->Value;
            }

            return $resultsObj;
        } else {
            throw new \Exception('Unable to fetch tables: '.$this->db->error);
        }
    }

	/**
	 * @return \stdClass
	 * @throws \Exception
	 */
    protected function fetchTables()
    {
        $result = $this->db->query('
        SELECT table_name AS tables
        FROM information_schema.tables
        WHERE table_schema = DATABASE()
        AND table_type != "VIEW"
        ');

        if ($result) {
            $resultsObj = new \stdClass();

            while ($row = $result->fetch_object()) {
                $resultsObj->{$row->tables} = $this->fetchFields($row->tables);
            }

            return $resultsObj;
        } else {
            throw new \Exception('Unable to fetch tables: '.$this->db->error);
        }
    }

	/**
	 * @param $table
	 * @return \stdClass
	 * @throws \Exception
	 */
    protected function fetchFields($table)
    {
        $result = $this->db->query('DESCRIBE '.$table.';');

        if ($result) {
            $resultsObj = new \stdClass();

            while ($row = $result->fetch_object()) {
                $row->foreignKeys = $this->fetchFieldConstraints($table, $row->Field);

                $resultsObj->{$row->Field} = $row;
            }

            return $resultsObj;
        } else {
            throw new \Exception('Unable to fetch table fields: '.$this->db->error);
        }
    }

	/**
	 * @param $table
	 * @param $field
	 * @return object|\stdClass
	 * @throws \Exception
	 */
    protected function fetchFieldConstraints($table, $field)
    {
        $query = '
        SELECT *
        FROM information_schema.key_column_usage
        WHERE referenced_table_name IS NOT NULL
        AND TABLE_NAME = "'.$table.'"
        AND COLUMN_NAME = "'.$field.'"
        LIMIT 1
        ';

        $result = $this->db->query($query);

        if ($result) {
            while ($row = $result->fetch_object()) {
                $row->actions = $this->fetchFieldConstraintsActions($row->CONSTRAINT_NAME);

                return $row;
            }
        } else {
            throw new \Exception('Unable to fetch field constraints: '.$this->db->error);
        }
    }

	/**
	 * @param $constraintName
	 * @return object|\stdClass
	 * @throws \Exception
	 */
    protected function fetchFieldConstraintsActions($constraintName)
    {
        $query = '
        SELECT *
        FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS
        WHERE CONSTRAINT_NAME = "'.$constraintName.'"
        LIMIT 1
        ';

        $result = $this->db->query($query);

        if ($result) {
            while ($row = $result->fetch_object()) {
                return $row;
            }
        } else {
            throw new \Exception('Unable to fetch field constraints actions: '.$this->db->error);
        }
    }

	/**
	 * @param $table
	 * @param $field
	 * @param $referencedTable
	 * @param $referencedField
	 * @return bool
	 * @throws \Exception
	 */
    public function foreignKeyRelationExists($table, $field, $referencedTable, $referencedField)
    {
        $query = '
        SELECT *
        FROM information_schema.key_column_usage
        WHERE referenced_table_name IS NOT NULL
        AND TABLE_NAME = "'.$table.'"
        AND COLUMN_NAME = "'.$field.'"
        AND REFERENCED_TABLE_NAME = "'.$referencedTable.'"
        AND REFERENCED_COLUMN_NAME = "'.$referencedField.'"
        LIMIT 1
        ';

        $result = $this->db->query($query);

        if ($result) {
            return (bool) $result->num_rows;
        } else {
            throw new \Exception('Unable to fetch field constraints actions: '.$this->db->error);
        }
    }
}
