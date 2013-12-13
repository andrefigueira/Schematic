<?php

/**
 * Schematic is a MySQL database creation and maintenance script, It allows you to define a schema in JSON and run a
 * simple script to do the creation or updates to your database, If you change your schema file and run the script it
 * will then run through and make the updates to the database.
 *
 * @author <Andre Figueira> andre.figueira@me.com
 * @package Schematic
 * @version 1.1
 *
 */

namespace Controllers;

class Schematic
{

    //Default schema directory
    private $dir = 'schemas';

    //The property which contains information of the schema
    private $schema;

    public $tableDir = '';

    //The generated schema SQL
    public $sql = '';

    public function __construct()
    {

        $this->schemaDir = dirname(dirname(__DIR__)) . '/' . $this->dir . '/';

    }

    /**
     * Creates a connection to mysql and sets the mysql object to a db property so it's available to the methods
     *
     * @internal param $db
     */
    public function connect()
    {

        $this->db = new \mysqli($this->schema->connection->host, $this->schema->connection->user, $this->schema->connection->pass);

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

        if($this->tableDir != ''){ $this->tableDir = $this->tableDir . '/';}

        $this->realSchemaDir = $this->schemaDir . $this->tableDir;

        if(is_dir($this->realSchemaDir))
        {

            if(!$this->isEmptyDir($this->realSchemaDir))
            {

                $files = scandir($this->realSchemaDir);

                foreach($files as $file)
                {

                    if($file != '.' && $file != '..')
                    {

                        $specificSchemaDir = $this->realSchemaDir . $file;
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

                                throw new \Exception('Unable to load schema file');

                            }

                        }
                        else
                        {

                            throw new \Exception('Schema json file does not exist: ' . $specificSchemaConfFile);

                        }

                    }

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

            throw new \Exception('Schema folder does not exist:' . $this->realSchemaDir);

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

        foreach($settings->fields as $field => $fieldSettings)
        {

            if(!isset($fieldSettings->index)){ $fieldSettings->index = '';}
            if(isset($fieldSettings->autoIncrement) && $fieldSettings->autoIncrement){ $fieldSettings->autoIncrement = 'AUTO_INCREMENT';}else{ $fieldSettings->autoIncrement = '';}
            if(isset($fieldSettings->null) && $fieldSettings->null){ $fieldSettings->null = 'NULL';}else{ $fieldSettings->null = 'NOT NULL';}
            if(isset($fieldSettings->unsigned) && $fieldSettings->unsigned){ $fieldSettings->unsigned = 'unsigned';}else{ $fieldSettings->unsigned = '';}

            $addFieldSql .= '
            `' . $field . '` ' . $fieldSettings->type . ' ' . $fieldSettings->unsigned . ' ' . $fieldSettings->null . ' ' . $fieldSettings->autoIncrement . ',';

            if(isset($fieldSettings->index) && $fieldSettings->index != '')
            {

                $indexesSql .= '
                ' . $fieldSettings->index . '(`' . $field . '`),';

            }

        }

        if($indexesSql == ''){ $addFieldSql = substr($addFieldSql, 0, -1);}

        $indexesSql = substr($indexesSql, 0, -1);

        //Query to create the table if it doesn't exist indicating a first time run
        $query = '
        CREATE TABLE IF NOT EXISTS `'. $table . '` (
          ' . $addFieldSql . '
          ' . $indexesSql . '
        ) ENGINE=' . $this->schema->database->general->engine . ' DEFAULT CHARSET=' . $this->schema->database->general->charset . ' COLLATE=' . $this->schema->database->general->collation . ';
        ';

        $this->db->select_db($this->schema->database->general->name);

        $result = $this->db->query($query);

        @file_put_contents($table . '.sql', $query);

        if($result)
        {

            echo 'Generated Schema Successfully table (' . $table . ') on database(' . $this->schema->database->general->name . ')' .PHP_EOL;

        }
        else
        {

            throw new \Exception('Failed to generate schema: ' . $this->db->error);

        }

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

        }

        $updateFieldSql = substr($updateFieldSql, 0, -1);

        //Query to update the table only if it already exists
        $query = '
        ALTER TABLE `' . $table . '`
        ' . $updateFieldSql . ';
        ';

        $this->db->select_db($this->schema->database->general->name);

        $result = $this->db->query($query);

        @file_put_contents($table . '.sql', $query);

        if($result)
        {

            echo 'Generated Schema Successfully table (' . $table . ') on database(' . $this->schema->database->general->name . ')' .PHP_EOL;

        }
        else
        {

            throw new \Exception('Failed to generate schema: ' . $this->db->error);

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

}