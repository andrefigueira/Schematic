<?php

/**
 *
 * Schematic is a MySQL database creation and maintenance script, It allows you to define a schema in JSON and run a
 * simple script to do the creation or updates to your database, If you change your schema file and run the script it
 * will then run through and make the updates to the database.
 *
 * @author <Andre Figueira> andre.figueira@me.com
 * @package Schematic
 * @version 1.0
 *
 */

namespace Controllers;

class Schematic
{

    //Default schema directory
    private $dir = 'schemas';

    //The property which contains information of the schema
    private $schema;

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

        if(is_dir($this->schemaDir))
        {

            if(!$this->isEmptyDir($this->schemaDir))
            {

                $files = scandir($this->schemaDir);

                foreach($files as $file)
                {

                    if($file != '.' && $file != '..')
                    {

                        $specificSchemaDir = $this->schemaDir . $file;
                        $specificSchemaConfFile = $specificSchemaDir . '/schema.json';

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

                            throw new \Exception('Schema json file does not exist');

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

            throw new \Exception('Schema folder does not exist');

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

            $this->generateTableSql($table, $settings);

            $this->createDb();

            $this->db->select_db($this->schema->database->general->name);

            $result = $this->db->query($this->sql);

            if($result)
            {

                echo 'Generated Schema Successfully';

            }
            else
            {

                throw new \Exception('Failed to generate schema: ' . $this->db->error);

            }

        }

    }

    /**
     * Checks the schema and generates the SQL for creation based on it
     *
     * @param $table
     * @param $settings
     * @throws \Exception
     *
     */
    public function generateTableSql($table, $settings)
    {

        $addFieldSql = '';
        $updateFieldSql = '';

        foreach($settings->fields as $field => $fieldSettings)
        {

            if(!$fieldSettings->index){ $fieldSettings->index = '';}
            if($fieldSettings->autoIncrement){ $fieldSettings->autoIncrement = 'AUTO_INCREMENT';}
            if($fieldSettings->null){ $fieldSettings->null = 'NULL';}else{ $fieldSettings->null = 'NOT NULL';}
            if($fieldSettings->unsigned){ $fieldSettings->unsigned = 'unsigned';}

            $addFieldSql .= '
            `' . $field . '` ' . $fieldSettings->type . '(' . $fieldSettings->length . ') ' . $fieldSettings->unsigned . ' ' . $fieldSettings->null . ' ' . $fieldSettings->autoIncrement . ',';

            if($fieldSettings->index != '')
            {

                $addFieldSql .= '
                ' . $fieldSettings->index . ' (`' . $field . '`),
                ';

            }

            $updateFieldSql .= '
            MODIFY COLUMN `' . $field . '` ' . $fieldSettings->type . '(' . $fieldSettings->length . ') ' . $fieldSettings->unsigned . ' ' . $fieldSettings->null . ' ' . $fieldSettings->autoIncrement . ',';

        }

        $addFieldSql = substr($addFieldSql, 0, -1);
        $updateFieldSql = substr($updateFieldSql, 0, -1);

        //Query to create the table if it doesn't exist indicating a first time run
        $this->sql .= '
        CREATE TABLE IF NOT EXISTS `'. $table . '` (
          ' . $addFieldSql . '
        ) ENGINE=' . $this->schema->database->general->engine . ' DEFAULT CHARSET=' . $this->schema->database->general->charset . ' COLLATE=' . $this->schema->database->general->collation . ';
        ';

        //Query to update the table only if it already exists
        $this->sql .= '
        ALTER TABLE `' . $table . '`
        ' . $updateFieldSql . '
        ';

        $put = @file_put_contents('test.sql', $this->sql);

        if(!$put){ throw new \Exception('Unable to write test file');}

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