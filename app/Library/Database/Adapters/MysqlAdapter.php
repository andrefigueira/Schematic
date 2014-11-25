<?php
/**
 * This MySQL adapter implements methods defined on the DatabaseInterface and allows the manipulation of the database
 * in a way which the Schematic tool needs, when trying to determine all parts of the database for imports and
 * for the migrations which are performed on it
 *
 * @author Andre Figueira <andre.figueira@me.com>
 */

namespace Library\Database\Adapters;

use Library\Database\AbstractDatabaseAdapter;
use Library\Database\DatabaseInterface;

class MysqlAdapter extends AbstractDatabaseAdapter implements DatabaseInterface
{

    /** @var resource The database resource */
    protected $db;

    protected $table;

    protected $settings;

    protected $foreignKeysSql;

    protected $schema;

    public function setSchema($schema)
    {

        $this->schema = $schema;

    }

    public function connect()
    {

        $this->db = new \mysqli($this->host, $this->username, $this->password);

        if($this->db->connect_errno){ throw new \Exception($this->db->connect_error);}

    }

    public function createDatabase()
    {

        if($this->dbName === '')
        {

            throw new \Exception('Database name cannot be empty');

        }
        else
        {

            $result = $this->db->query('CREATE DATABASE IF NOT EXISTS `' . $this->dbName . '`');

            return $result;

        }

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

    public function tableExists()
    {

        $this->selectDb();

        $result = $this->db->query('SHOW TABLES LIKE "' . $this->table . '"');

        if($result)
        {

            return (bool) $result->num_rows;

        }
        else
        {

            throw new \Exception('Unable to check if table exists: ' . $this->db->error);

        }

    }

    public function fieldExists($field)
    {

        $result = $this->db->query('
        SELECT *
        FROM information_schema.COLUMNS
        WHERE
        TABLE_SCHEMA = "' . $this->dbName . '"
        AND TABLE_NAME = "' . $this->table . '"
        AND COLUMN_NAME = "' . $field . '"
        LIMIT 1
        ');

        if($result)
        {

            return (bool) $result->num_rows;

        }
        else
        {

            throw new \Exception('Failure in checking if column exists: '. $this->db->error);

        }

    }

    public function indexExists($field)
    {

        $result = $this->db->query('
        SELECT *
        FROM information_schema.COLUMNS
        WHERE
        TABLE_SCHEMA = "' . $this->dbName . '"
        AND TABLE_NAME = "' . $this->table . '"
        AND COLUMN_NAME = "' . $field . '"
        LIMIT 1
        ');

        if($result)
        {

            return (bool) $result->num_rows;

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

            throw new \Exception('Failed to run query: ' . $query . ' : ' . $this->db->error);

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

            $dbError = $this->db->error;

            throw new \Exception('Failed to run multiquery: ' . $dbError);

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

    public function mapDatabase()
    {

        $this->selectDb();

        return $this->fetchTables();

    }

    public function fetchDatabaseVariables()
    {

        $result = $this->db->query('SHOW variables;');

        if($result)
        {

            $resultsObj = new \stdClass();

            while($row = $result->fetch_object())
            {

                $resultsObj->{$row->Variable_name} = $row->Value;

            }

            return $resultsObj;

        }
        else
        {

            throw new \Exception('Unable to fetch tables: ' . $this->db->error);

        }

    }

    private function fetchTables()
    {

        $result = $this->db->query('
        SELECT table_name AS tables
        FROM information_schema.tables
        WHERE table_schema = DATABASE()
        ');

        if($result)
        {

            $resultsObj = new \stdClass();

            while($row = $result->fetch_object())
            {

                $resultsObj->{$row->tables} = $this->fetchFields($row->tables);

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

    public function foreignKeyRelationExists($table, $field, $referencedTable, $referencedField)
    {

        $query = '
        SELECT *
        FROM information_schema.key_column_usage
        WHERE referenced_table_name IS NOT NULL
        AND TABLE_NAME = "' . $table . '"
        AND COLUMN_NAME = "' . $field . '"
        AND REFERENCED_TABLE_NAME = "' . $referencedTable . '"
        AND REFERENCED_COLUMN_NAME = "' . $referencedField . '"
        LIMIT 1
        ';

        $result = $this->db->query($query);

        if($result)
        {

            return (bool)$result->num_rows;

        }
        else
        {

            throw new \Exception('Unable to fetch field constraints actions: ' . $this->db->error);

        }

    }

    public function migrateTable($table, $settings)
    {

        $this->table = $table;
        $this->settings = $settings;

        if($this->tableExists())
        {

            $result = $this->updateTable();

        }
        else
        {

            $result = $this->createTable();

        }

        return $result;

    }

    public function createTable()
    {

        $query = $this->generateCreateTableSql();

        $result = $this->query($query);

        return $result;

    }

    public function normalizeFieldSettings($settings)
    {


        if(isset($settings->index) == false)
        {

            $settingsIndex = '';

        }
        else
        {

            $settingsIndex = $settings->index;

        }

        if(isset($settings->autoIncrement) && $settings->autoIncrement)
        {

            $settingsAutoIncrement = 'AUTO_INCREMENT';

        }
        else
        {

            $settingsAutoIncrement = '';

        }

        if(isset($settings->null) && $settings->null)
        {

            $settingsNull = 'NULL';

        }
        else
        {

            $settingsNull = 'NOT NULL';

        }

        if(isset($settings->unsigned) && $settings->unsigned)
        {

            $settingsUnsigned = 'unsigned';

        }
        else
        {

            $settingsUnsigned = '';

        }

        $settings->index = $settingsIndex;
        $settings->autoIncrement = $settingsAutoIncrement;
        $settings->null = $settingsNull;
        $settings->unsigned = $settingsUnsigned;

        return $settings;

    }

    public function generateForeignKeysSql($field, $settings)
    {

        return '
        ALTER TABLE ' . $this->table . '
        ADD CONSTRAINT FOREIGN KEY (' . $field . ')
        REFERENCES ' . $settings->foreignKey->table . ' (' . $settings->foreignKey->field . ')
        ON DELETE ' . $settings->foreignKey->on->delete . '
        ON UPDATE ' . $settings->foreignKey->on->update . ';
        ';

    }

    public function generateIndexesSql($type, $indexesArray)
    {

        $indexesSql = '';
        $prefix = '';

        $primaryKeys = array();
        $uniqueKeys = array();
        $indexKeys = array();

        foreach($indexesArray as $indexType => $indexes)
        {

            switch($indexType)
            {

                case 'PRIMARY_KEY':
                    foreach($indexes as $index){ array_push($primaryKeys, $index['field']);}
                    break;

                case 'UNIQUE_KEY':
                    foreach($indexes as $index){ array_push($uniqueKeys, $index['field']);}
                    break;

                default:
                    foreach($indexes as $index){ array_push($indexKeys, $index['field']);}


            }

        }

        if($type == 'update'){ $prefix = 'ADD ';}

        if(count($primaryKeys) > 0){ $indexesSql .= ', ' . $prefix . ' PRIMARY KEY (' . implode(', ', $primaryKeys)  . ')';}
        if(count($uniqueKeys) > 0){ $indexesSql .= ',' . $prefix . ' UNIQUE KEY (' . implode(', ', $uniqueKeys)  . ')';}
        if(count($indexKeys) > 0){ $indexesSql .= ',' . $prefix . ' INDEX (' . implode(', ', $indexKeys)  . ')';}

        return $indexesSql;

    }

    public function generateCreateTableSql()
    {

        $addFieldSql = '';
        $delimeter = '';
        $i = 1;

        foreach($this->settings->fields as $field => $settings)
        {

            $settings = $this->normalizeFieldSettings($settings);

            if(isset($this->foreignKey)){ $this->foreignKeysSql .= $this->generateForeignKeysSql($field, $settings);}

            if(isset($settings->index) && $settings->index != '')
            {

                $fieldKey = str_replace(' ', '_', $settings->index);

                $indexesArray[$fieldKey][] = array(
                    'type' => $settings->index,
                    'field' => $field
                );

            }

            if($i != 1){ $delimeter = ',';}

            $addFieldSql .= $delimeter . '
            `' . $field . '` ' . $settings->type . ' ' . $settings->unsigned . ' ' . $settings->null . ' ' . $settings->autoIncrement;

            $i++;

        }

        $indexesSql = $this->generateIndexesSql('create', $indexesArray);

        $query = '
        CREATE TABLE IF NOT EXISTS  ' . $this->table . ' (
        ' . $addFieldSql . '
        ' . $indexesSql . '
        ) ENGINE=' . $this->schema->database->general->engine . ' DEFAULT CHARSET=' . $this->schema->database->general->charset . ' COLLATE=' . $this->schema->database->general->collation . ';
        ';

        return $query;

    }

    public function generateUpdateTableSql()
    {

        $updateFieldSql = '';
        $previousColumn = '';
        $columnOrdering = '';
        $delimeter = '';
        $i = 1;

        foreach($this->settings->fields as $field => $settings)
        {

            if($i != 1){ $delimeter = ',';}

            $settings = $this->normalizeFieldSettings($settings);

            if($this->fieldExists($field) || (isset($settings->rename) && $this->dbAdapter->fieldExists($settings->rename)))
            {

                if(isset($settings->rename))
                {

                    if($this->fieldExists($settings->rename))
                    {

                        if($previousColumn != ''){ $columnOrdering = ' AFTER `' . $previousColumn . '`';}

                        $updateFieldSql .= $delimeter . '
                        MODIFY COLUMN `' . $settings->rename . '` ' . $settings->type . ' ' . $settings->unsigned . ' ' . $settings->null . ' ' . $settings->autoIncrement . $columnOrdering;

                        $previousColumn = $settings->rename;

                    }
                    else
                    {

                        if($previousColumn != ''){ $columnOrdering = ' AFTER `' . $previousColumn . '`';}

                        $updateFieldSql .= $delimeter . '
                        CHANGE COLUMN `' . $field . '` `' . $settings->rename . '` ' . $settings->type . ' ' . $settings->unsigned . ' ' . $settings->null . ' ' . $settings->autoIncrement . $columnOrdering;

                        $previousColumn = $settings->rename;

                    }

                }
                else
                {

                    if($previousColumn != ''){ $columnOrdering = ' AFTER `' . $previousColumn . '`';}

                    $updateFieldSql .= $delimeter  . '
                    MODIFY COLUMN `' . $field . '` ' . $settings->type . ' ' . $settings->unsigned . ' ' . $settings->null . ' ' . $settings->autoIncrement . $columnOrdering;

                    $previousColumn = $field;

                }

            }
            else
            {

                if($previousColumn != ''){ $columnOrdering = ' AFTER `' . $previousColumn . '`';}

                $updateFieldSql .= $delimeter . 'ADD COLUMN `' . $field . '` ' . $settings->type . ' ' . $settings->unsigned . ' ' . $settings->null . ' ' . $settings->autoIncrement . $columnOrdering;

            }

            if(isset($this->foreignKey))
            {

                if(!$this->foreignKeyRelationExists($this->table, $field, $settings->foreignKey->table, $settings->foreignKey->field))
                {

                    $this->foreignKeysSql .= $this->generateForeignKeysSql($field, $settings);

                }

            }

            if(isset($settings->index) && $settings->index != '')
            {

                $fieldKey = str_replace(' ', '_', $settings->index);

                $indexesArray[$fieldKey][] = array(
                    'type' => $settings->index,
                    'field' => $field
                );

            }

            $i++;

        }

        $indexesSql = $this->generateIndexesSql('update', $indexesArray);
        $indexesSql = '';

        $query = '
        ALTER TABLE `' . $this->table . '`
        ' . $updateFieldSql . '
        ' . $indexesSql . ';';

        return $query;

    }

    public function updateTable()
    {

        $this->deleteNonSchemedFields();

        $query = $this->generateUpdateTableSql();

        $result = $this->query($query);

        return $result;

    }

    public function deleteNonSchemedFields()
    {

        $deleteSql = '';
        $tableFields = $this->showFields($this->table);
        $newFields = array();
        $unschemedFields = array();

        foreach($this->settings->fields as $field => $settings)
        {

            if(isset($settings->rename))
            {

                array_push($newFields, $settings->rename);

            }

            array_push($newFields, $field);

        }

        foreach($tableFields as $field)
        {

            if(!in_array($field, $newFields))
            {

                $deleteSql .= 'ALTER TABLE ' . $this->table . ' DROP ' .$field . ';';

                array_push($unschemedFields, $field);

            }

        }

        if($deleteSql != '')
        {

            return $this->multiQuery($deleteSql);

        }

    }

    public function applyForeignKeys()
    {

        if($this->foreignKeysSql != '')
        {

            if($this->multiQuery($this->foreignKeysSql))
            {


            }
            else
            {



            }

        }
        else
        {



        }

    }

}