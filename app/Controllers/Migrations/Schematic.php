<?php

/**
 * Schematic is a MySQL database creation and maintenance script, It allows you to define a schema in JSON and run a
 * simple script to do the creation or updates to your database, If you change your schema file and run the script it
 * will then run through and make the updates to the database.
 *
 * @author <Andre Figueira> andre.figueira@me.com
 * @package Schematic
 * @version 1.2.6
 *
 */

namespace Controllers\Migrations;

class Schematic
{

    /** @var string The base directory for the schematic install */
    protected $baseDir = '';

    /** @var string The default schema directory */
    protected $schemaDir = '';

    /** @var The property which contains information of the schema */
    protected $schema;

    /** @var string The directory for the tables */
    protected $tableDir = '';

    /** @var string The generated schema SQL */
    protected $sql = '';

    /** @var string The default directory for the generated SQL */
    protected $sqlDir = './sql/';

    /** @var string The real directory for the schema files */
    protected $realSchemaDir = '';

    /** @var string The name of the schema file to load up */
    protected $schemaFile = '';

    protected $db;

    protected $environment;

    protected $environmentConfigs;

    /**
     * Set up the schema dir and create an instance of the log
     */
    public function __construct()
    {

        $this->log = new Log();

    }

    public function setDir($dir)
    {

        $this->schemaDir = $dir;

        return $this;

    }

    public function setEnvironmentConfigs($environment)
    {

        $this->environment = $environment;

        $this->bindEnvironmentConfigs();

        return $this;

    }

    private function bindEnvironmentConfigs()
    {

        $environmentPath = $this->schemaDir . 'config/';
        $environmentFile = $environmentPath . $this->environment . '.json';

        if(($environmentFile))
        {

            $this->environmentConfigs = @file_get_contents($environmentFile);
            $this->environmentConfigs = json_decode($this->environmentConfigs);

        }
        else
        {

            throw new \Exception('Unable to load environment configs file: ' . $environmentFile);

        }

    }

    public function setSchemaFile($schemaFile)
    {

        $this->schemaFile = $schemaFile;

    }

    /**
     * Creates a connection to mysql and sets the mysql object to a db property so it's available to the methods
     *
     * @internal param $db
     */
    public function connect()
    {

        $this->db = new \mysqli($this->environmentConfigs->host, $this->environmentConfigs->user, $this->environmentConfigs->pass);

        if($this->db->connect_errno){ throw new \Exception($this->db->connect_error);}

    }

    /**
     * Checks if the schema directory exists, checks if the schema.json exists, after fetched the json contents and saves
     * the json as a recursive object with all of the schema properties
     *
     * @return bool
     * @throws \Exception
     *
     */
    public function exists()
    {

        $this->realSchemaDir = $this->schemaDir;

        if(is_dir($this->realSchemaDir))
        {

            if(!$this->isEmptyDir($this->realSchemaDir))
            {

                $files = scandir($this->realSchemaDir);

                $specificSchemaDir = $this->realSchemaDir . $this->schemaFile;
                $specificSchemaConfFile = $specificSchemaDir;

                if(file_exists($specificSchemaConfFile))
                {

                    $this->schema = @file_get_contents($specificSchemaConfFile);

                    if($this->schema)
                    {

                        $this->schema = json_decode($this->schema);

                    }
                    else
                    {

                        throw new \Exception('Unable to load schema file: ' . $specificSchemaConfFile);

                    }

                }
                else
                {

                    throw new \Exception('Schema json file does not exist: ' . $specificSchemaConfFile);

                }

                return true;

            }
            else
            {

                throw new \Exception('No schemas in folder');

            }

        }
        else
        {

            throw new \Exception('Schema folder does not exist: ' . $this->realSchemaDir);

        }

    }

    /**
     * Runs a query to create the database if it does not yet exist
     *
     * @return bool
     * @throws \Exception
     *
     */
    private function createDb()
    {

        $result = $this->db->query('CREATE DATABASE IF NOT EXISTS `' . $this->schema->database->general->name . '`');

        if($result)
        {

            $this->log->write('Created database ' . $this->schema->database->general->name);

            return true;

        }
        else
        {

            throw new \Exception('Unable to create database: ' . $this->db->error);

        }

    }

    /**
     * Creates the table if it doesn't exist
     *
     * @param $table
     * @param $settings
     * @throws \Exception
     *
     */
    private function createTable($table, $settings)
    {

        $addFieldSql = '';
        $indexesSql = '';
        $foreignKeysSql = '';
        $data = array();

        foreach($settings->fields as $field => $fieldSettings)
        {

            if(!isset($fieldSettings->index)){ $fieldSettings->index = '';}
            if(isset($fieldSettings->autoIncrement) && $fieldSettings->autoIncrement){ $fieldSettings->autoIncrement = 'AUTO_INCREMENT';}else{ $fieldSettings->autoIncrement = '';}
            if(isset($fieldSettings->null) && $fieldSettings->null){ $fieldSettings->null = 'NULL';}else{ $fieldSettings->null = 'NOT NULL';}
            if(isset($fieldSettings->unsigned) && $fieldSettings->unsigned){ $fieldSettings->unsigned = 'unsigned';}else{ $fieldSettings->unsigned = '';}

            if(isset($fieldSettings->foreignKey))
            {

                $foreignKeysSql .= '
                ,
                FOREIGN KEY (' . $field . ')
                REFERENCES ' . $fieldSettings->foreignKey->table . ' (' . $fieldSettings->foreignKey->field . ')
                ON DELETE ' . $fieldSettings->foreignKey->on->delete . '
                ON UPDATE ' . $fieldSettings->foreignKey->on->update . '
                ';

            }

            $addFieldSql .= '
            `' . $field . '` ' . $fieldSettings->type . ' ' . $fieldSettings->unsigned . ' ' . $fieldSettings->null . ' ' . $fieldSettings->autoIncrement . ',';

            if(isset($fieldSettings->index) && $fieldSettings->index != '')
            {

                $indexesSql .= '
                ' . $fieldSettings->index . '(`' . $field . '`),';

            }

            array_push($data, array(
                'table' => $table,
                'index' => $fieldSettings->index,
                'field' => $field,
                'type' => $fieldSettings->type,
                'null' => $fieldSettings->null,
                'autoIncrement' => $fieldSettings->autoIncrement,
                'unsigned' => $fieldSettings->unsigned
            ));

        }

        if($indexesSql == ''){ $addFieldSql = substr($addFieldSql, 0, -1);}

        $indexesSql = substr($indexesSql, 0, -1);

        //Query to create the table if it doesn't exist indicating a first time run
        $query = '
        CREATE TABLE IF NOT EXISTS `'. $table . '` (
          ' . $addFieldSql . '
          ' . $indexesSql . '
          ' . $foreignKeysSql . '
        ) ENGINE=' . $this->schema->database->general->engine . ' DEFAULT CHARSET=' . $this->schema->database->general->charset . ' COLLATE=' . $this->schema->database->general->collation . ';
        ';

        $this->db->select_db($this->schema->database->general->name);

        $result = $this->db->query($query);

        $this->createSqlFile($table, $query);

        if($result)
        {

            $message = 'Generated Schema Successfully table (' . $table . ') on database(' . $this->schema->database->general->name . ')';

            $this->log->write($message);

        }
        else
        {

            throw new \Exception('Failed to generate schema: ' . $this->db->error);

        }

    }

    public function createSqlFile($table, $query)
    {

        if(!is_dir($this->sqlDir))
        {

            $newDir = @mkdir($this->sqlDir);

            if(!$newDir){ throw new \Exception('Unable to create new SQL directory: ' . $this->sqlDir);}

        }

        $file = $this->sqlDir . $table . '.sql';

        $sql = @file_put_contents($file, $query);

        if(!$sql){ throw new \Exception('Unable to create SQL file: ' . $file);}

        return true;

    }

    /**
     * Update the table add columns which don't exist or modify existing columns
     *
     * @param $table
     * @param $settings
     * @throws \Exception
     *
     */
    private function updateTable($table, $settings)
    {

        $updateFieldSql = '';

        $data = array();

        foreach($settings->fields as $field => $fieldSettings)
        {

            if(!isset($fieldSettings->index)){ $fieldSettings->index = '';}
            if(isset($fieldSettings->autoIncrement) && $fieldSettings->autoIncrement){ $fieldSettings->autoIncrement = 'AUTO_INCREMENT';}else{ $fieldSettings->autoIncrement = '';}
            if(isset($fieldSettings->null) && $fieldSettings->null){ $fieldSettings->null = 'NULL';}else{ $fieldSettings->null = 'NOT NULL';}
            if(isset($fieldSettings->unsigned) && $fieldSettings->unsigned){ $fieldSettings->unsigned = 'unsigned';}else{ $fieldSettings->unsigned = '';}

            if($this->fieldExists($table, $field))
            {

                $updateFieldSql .= '
                MODIFY COLUMN `' . $field . '` ' . $fieldSettings->type . ' ' . $fieldSettings->unsigned . ' ' . $fieldSettings->null . ' ' . $fieldSettings->autoIncrement . ',';

            }
            else
            {

                $updateFieldSql .= '
                ADD COLUMN `' . $field . '` ' . $fieldSettings->type . ' ' . $fieldSettings->unsigned . ' ' . $fieldSettings->null . ' ' . $fieldSettings->autoIncrement . ',';

            }

            array_push($data, array(
                'table' => $table,
                'index' => $fieldSettings->index,
                'field' => $field,
                'type' => $fieldSettings->type,
                'null' => $fieldSettings->null,
                'autoIncrement' => $fieldSettings->autoIncrement,
                'unsigned' => $fieldSettings->unsigned
            ));

        }

        $updateFieldSql = substr($updateFieldSql, 0, -1);

        //Query to update the table only if it already exists
        $query = '
        ALTER TABLE `' . $table . '`
        ' . $updateFieldSql . ';
        ';

        $this->db->select_db($this->schema->database->general->name);

        $this->deleteNonSchemaFields($table, $settings);

        $result = $this->db->query($query);

        $this->createSqlFile($table, $query);

        if($result)
        {

            $message = 'Generated Schema Successfully table (' . $table . ') on database(' . $this->schema->database->general->name . ')';

            $this->log->write($message);

        }
        else
        {

            throw new \Exception('Failed to generate schema: ' . $this->db->error);

        }

    }

    private function deleteNonSchemaFields($table, $settings)
    {

        $this->db->select_db($this->schema->database->general->name);

        $deleteSql = '';
        $tableFields = $this->showFields($table);
        $newFields = array();
        $unschemedFields = array();

        foreach($settings->fields as $field => $fieldSettings)
        {

            array_push($newFields, $field);

        }

        foreach($tableFields as $field)
        {

            if(!in_array($field, $newFields))
            {

                $deleteSql .= 'ALTER TABLE `' . $table . '` DROP `' .$field . '`;';

                array_push($unschemedFields, $field);

            }

        }

        if($deleteSql != '')
        {

            $result = $this->db->multi_query($deleteSql);

            if($result)
            {

                $message = 'Deleted Unschemed fields (' . implode(', ', $unschemedFields) . ')';

                $this->log->write($message);

                echo $message . PHP_EOL;

            }
            else
            {

                throw new \Exception('Failed to delete unschemed fields: ' . $this->db->error);

            }

        }

    }

    private function tableExists($table)
    {

        $this->db->select_db($this->schema->database->general->name);

        $result = $this->db->query('SHOW TABLES LIKE "' . $table . '"');

        if($result)
        {

            return (bool)$result->num_rows;

        }
        else
        {

            throw new \Exception('Unable to check if table exists: ' . $this->db->error);

        }

    }

    /**
     * Sets up the MySQL connection, runs the table generation and builds the query then runs it
     *
     * @throws \Exception
     *
     */
    public function generate()
    {

        $this->connect();

        foreach($this->schema->database->tables as $table => $settings)
        {

            $this->createDb();

            if($this->tableExists($table))
            {

                $this->updateTable($table, $settings);

            }
            else
            {

                $this->createTable($table, $settings);

            }

        }

    }

    /**
     * Checks the table of the database to see if the column passed already exists
     *
     * @param $table
     * @param $field
     * @return bool
     * @throws \Exception
     *
     */
    private function fieldExists($table, $field)
    {

        $result = $this->db->query('
        SELECT *
        FROM information_schema.COLUMNS
        WHERE
        TABLE_SCHEMA = "' . $this->schema->database->general->name . '"
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

    /**
     * Checks to see if the directory is empty
     *
     * @param $dir
     * @return bool|null
     *
     */
    public function isEmptyDir($dir)
    {

        if(!is_readable($dir)) return null;
        return (count(scandir($dir)) == 2);

    }

    public function showFields($table)
    {

        $this->db->select_db($this->schema->database->general->name);

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

}