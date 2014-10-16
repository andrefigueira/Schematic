<?php
/**
 * Schematic is a MySQL database creation and maintenance script, It allows you to define a schema in JSON and run a
 * simple script to do the creation or updates to your database, If you change your schema file and run the script it
 * will then run through and make the updates to the database.
 *
 * @author <Andre Figueira> andre.figueira@me.com
 * @package Schematic
 * @version 1.4.1
 *
 */

namespace Controllers\Migrations;

use Controllers\Database\DatabaseInterface;
use Controllers\Logger\LogInterface;
use Controllers\Cli\OutputInterface;

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

    /** @var string The db name */
    protected $db;

    /** @var DatabaseInterface The Database adapter currently in use */
    protected $dbAdapter;

    /** @var string The environment that the Schematic is running on */
    protected $environment;

    /** @var object Object of properties pertainent to the database connected to */
    protected $environmentConfigs;

    protected $foreignKeysSql;

    protected $indexesArray;

    /**
     * We're injecting a logger and a database adapter into the Schematic which are interchangeable
     *
     * @param LogInterface $log
     * @param DatabaseInterface $dbAdapter
     * @param OutputInterface $output
     */
    public function __construct(LogInterface $log, DatabaseInterface $dbAdapter, OutputInterface $output)
    {

        $this->log = $log;
        $this->dbAdapter = $dbAdapter;
        $this->output = $output;

        return $this;

    }

    /**
     * Setter for the working directory
     *
     * @param $dir
     * @return $this
     */
    public function setDir($dir)
    {

        $this->schemaDir = $dir;

        $this->output->writeln('Set directory to: ' . $dir);

        return $this;

    }

    /**
     * Setter and binder of environment configs for the database for which we are managing
     *
     * @param $environment
     * @return $this
     * @throws \Exception
     */
    public function setEnvironmentConfigs($environment)
    {

        $this->environment = $environment;

        $this->bindEnvironmentConfigs();

        return $this;

    }

    /**
     * Bind the environment configs to properties
     *
     * @throws \Exception
     */
    private function bindEnvironmentConfigs()
    {

        $environmentPath = $this->schemaDir . 'config/';
        $environmentFile = $environmentPath . $this->environment . '.json';

        if($environmentFile)
        {

            $this->environmentConfigs = @file_get_contents($environmentFile);
            $this->environmentConfigs = json_decode($this->environmentConfigs);

            $this->setDbAdapterConfigs();

        }
        else
        {

            throw new \Exception('Unable to load environment configs file: ' . $environmentFile);

        }

    }

    /**
     * Set the schema file to be used currently
     *
     * @param $schemaFile
     */
    public function setSchemaFile($schemaFile)
    {

        $this->schemaFile = $schemaFile;

    }

    /**
     * Gets the current schema object which is relevant to the current file proecting
     *
     * @return Object
     */
    public function getSchema()
    {

        return $this->schema;

    }

    /**
     * Sets up database adapter configurations
     *
     * @return void
     */
    private function setDbAdapterConfigs()
    {

        $this->dbAdapter
            ->setHost($this->environmentConfigs->host)
            ->setUsername($this->environmentConfigs->user)
            ->setPassword($this->environmentConfigs->pass)
            ->connect();

    }

    /**
     * Checks if the schema file exists, if it does, assigns the json_decoded schema file to a schema property within the instance
     *
     * @throws \Exception
     */
    public function exists()
    {

        $this->realSchemaDir = $this->schemaDir;

        if(is_dir($this->realSchemaDir))
        {

            if(!$this->isEmptyDir($this->realSchemaDir))
            {

                $specificSchemaDir = $this->realSchemaDir . $this->schemaFile;
                $specificSchemaConfFile = $specificSchemaDir;

                if(file_exists($specificSchemaConfFile))
                {

                    $this->output->writeln('Loading schema file: ' . $specificSchemaConfFile);

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

        return $this->dbAdapter->createDatabase($this->schema->database->general->name);

    }

    /**
     * Creates the table if it doesn't exist
     *
     * @param $table
     * @param $settings
     * @throws \Exception
     */
    private function createTable($table, $settings)
    {

        $addFieldSql = '';
        $indexesSql = '';
        $indexesArray = array();

        foreach($settings->fields as $field => $fieldSettings)
        {

            if(!isset($fieldSettings->index)){ $fieldSettings->index = '';}
            if(isset($fieldSettings->autoIncrement) && $fieldSettings->autoIncrement){ $fieldSettings->autoIncrement = 'AUTO_INCREMENT';}else{ $fieldSettings->autoIncrement = '';}
            if(isset($fieldSettings->null) && $fieldSettings->null){ $fieldSettings->null = 'NULL';}else{ $fieldSettings->null = 'NOT NULL';}
            if(isset($fieldSettings->unsigned) && $fieldSettings->unsigned){ $fieldSettings->unsigned = 'unsigned';}else{ $fieldSettings->unsigned = '';}

            if(isset($fieldSettings->foreignKey))
            {

                $this->foreignKeysSql .= '
                ALTER TABLE ' . $table . '
                    ADD CONSTRAINT FOREIGN KEY (' . $field . ')
                    REFERENCES ' . $fieldSettings->foreignKey->table . ' (' . $fieldSettings->foreignKey->field . ')
                    ON DELETE ' . $fieldSettings->foreignKey->on->delete . '
                    ON UPDATE ' . $fieldSettings->foreignKey->on->update . ';
                ';

            }

            $addFieldSql .= '
            `' . $field . '` ' . $fieldSettings->type . ' ' . $fieldSettings->unsigned . ' ' . $fieldSettings->null . ' ' . $fieldSettings->autoIncrement . ',';

            if(isset($fieldSettings->index) && $fieldSettings->index != '')
            {

                $fieldKey = str_replace(' ', '_', $fieldSettings->index);

                $indexesArray[$fieldKey][] = array(
                    'type' => $fieldSettings->index,
                    'field' => $field
                );

            }

        }

        $primaryKeys = array();
        $uniqueKeys = array();
        $indexKeys = array();

        foreach($indexesArray as $indexType => $indexes)
        {

            switch($indexType)
            {

                case 'PRIMARY_KEY':
                    foreach($indexes as $index)
                    {

                        array_push($primaryKeys, $index['field']);

                    }
                    break;

                case 'UNIQUE_KEY':
                    foreach($indexes as $index)
                    {

                        array_push($uniqueKeys, $index['field']);

                    }
                    break;

                default:
                    foreach($indexes as $index)
                    {

                        array_push($indexKeys, $index['field']);

                    }


            }

        }

        if(count($primaryKeys) > 0){ $indexesSql .= 'PRIMARY KEY (' . implode(', ', $primaryKeys)  . '),';}
        if(count($uniqueKeys) > 0){ $indexesSql .= 'UNIQUE KEY (' . implode(', ', $uniqueKeys)  . '),';}
        if(count($indexKeys) > 0){ $indexesSql .= 'INDEX (' . implode(', ', $indexKeys)  . '),';}

        unset($indexesArray);

        if($indexesSql == ''){ $addFieldSql = substr($addFieldSql, 0, -1);}

        $indexesSql = substr($indexesSql, 0, -1);

        //Query to create the table if it doesn't exist indicating a first time run
        $query = '
        CREATE TABLE IF NOT EXISTS '. $table . ' (
          ' . $addFieldSql . '
          ' . $indexesSql . '
        ) ENGINE=' . $this->schema->database->general->engine . ' DEFAULT CHARSET=' . $this->schema->database->general->charset . ' COLLATE=' . $this->schema->database->general->collation . ';
        ';

        $result = $this->dbAdapter->query($query);

        if($result)
        {

            $this->createSqlFile($table, $query);

            $message = 'Generated Schema Successfully table (' . $table . ') on database (' . $this->schema->database->general->name . ')';

            $this->log->write($message);
            $this->output->writeln($message);

        }

    }

    /**
     * Creates SQL file with the query which was last executed
     *
     * @param $table
     * @param $query
     * @return bool
     * @throws \Exception
     */
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
        $indexesSql = '';
        $foreignKeysSql = '';
        $indexesArray = array();

        foreach($settings->fields as $field => $fieldSettings)
        {

            if(!isset($fieldSettings->index)){ $fieldSettings->index = '';}
            if(isset($fieldSettings->autoIncrement) && $fieldSettings->autoIncrement){ $fieldSettings->autoIncrement = 'AUTO_INCREMENT';}else{ $fieldSettings->autoIncrement = '';}
            if(isset($fieldSettings->null) && $fieldSettings->null){ $fieldSettings->null = 'NULL';}else{ $fieldSettings->null = 'NOT NULL';}
            if(isset($fieldSettings->unsigned) && $fieldSettings->unsigned){ $fieldSettings->unsigned = 'unsigned';}else{ $fieldSettings->unsigned = '';}

            if($this->dbAdapter->fieldExists($table, $field))
            {

                $updateFieldSql .= '
                MODIFY COLUMN `' . $field . '` ' . $fieldSettings->type . ' ' . $fieldSettings->unsigned . ' ' . $fieldSettings->null . ' ' . $fieldSettings->autoIncrement . ',';

            }
            else
            {

                $updateFieldSql .= '
                ADD COLUMN `' . $field . '` ' . $fieldSettings->type . ' ' . $fieldSettings->unsigned . ' ' . $fieldSettings->null . ' ' . $fieldSettings->autoIncrement . ',';

            }

            if(isset($fieldSettings->foreignKey))
            {

                if(!$this->dbAdapter->foreignKeyRelationExists($table, $field, $fieldSettings->foreignKey->table, $fieldSettings->foreignKey->field))
                {

                    $foreignKeysSql .= '
                    ALTER TABLE ' . $table . '
                    ADD CONSTRAINT FOREIGN KEY (' . $field . ')
                    REFERENCES ' . $fieldSettings->foreignKey->table . ' (' . $fieldSettings->foreignKey->field . ')
                    ON DELETE ' . $fieldSettings->foreignKey->on->delete . '
                    ON UPDATE ' . $fieldSettings->foreignKey->on->update . ';
                    ';

                }

            }

            if(isset($fieldSettings->index) && $fieldSettings->index != '')
            {

                $fieldKey = str_replace(' ', '_', $fieldSettings->index);

                $indexesArray[$fieldKey][] = array(
                    'type' => $fieldSettings->index,
                    'field' => $field
                );

            }

        }

        $primaryKeys = array();
        $uniqueKeys = array();
        $indexKeys = array();

        foreach($indexesArray as $indexType => $indexes)
        {

            switch($indexType)
            {

                case 'PRIMARY_KEY':
                    foreach($indexes as $index)
                    {

                        array_push($primaryKeys, $index['field']);

                    }
                    break;

                case 'UNIQUE_KEY':
                    foreach($indexes as $index)
                    {

                        array_push($uniqueKeys, $index['field']);

                    }
                    break;

                default:
                    foreach($indexes as $index)
                    {

                        array_push($indexKeys, $index['field']);

                    }


            }

        }

        if(count($primaryKeys) > 0){ $indexesSql .= 'PRIMARY KEY (' . implode(', ', $primaryKeys)  . '),';}
        if(count($uniqueKeys) > 0){ $indexesSql .= 'UNIQUE KEY (' . implode(', ', $uniqueKeys)  . '),';}
        if(count($indexKeys) > 0){ $indexesSql .= 'INDEX (' . implode(', ', $indexKeys)  . '),';}

        unset($indexesArray);

        $indexesSql = '';

        if($indexesSql == ''){ $updateFieldSql = substr($updateFieldSql, 0, -1);}

        $indexesSql = substr($indexesSql, 0, -1);

        //Query to update the table only if it already exists
        $query = '
        ALTER TABLE ' . $table . '
        ' . $updateFieldSql . '
        ' . $indexesSql . '
        ';

        $this->deleteNonSchemaFields($table, $settings);

        $result = $this->dbAdapter->query($query);

        if($result)
        {

            $this->createSqlFile($table, $query);

            $message = 'Generated Schema Successfully table (' . $table . ') on database (' . $this->schema->database->general->name . ')';

            $this->log->write($message);
            $this->output->writeln($message);

        }

    }

    /**
     * Deletes fields which are in the database but not in the Schema file
     *
     * @param $table
     * @param $settings
     */
    private function deleteNonSchemaFields($table, $settings)
    {

        $deleteSql = '';
        $tableFields = $this->dbAdapter->showFields($table);
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

                $deleteSql .= 'ALTER TABLE ' . $table . ' DROP ' .$field . ';';

                array_push($unschemedFields, $field);

            }

        }

        if($deleteSql != '')
        {

            if($this->dbAdapter->multiQuery($deleteSql))
            {

                $message = 'Deleted Unschemed fields (' . implode(', ', $unschemedFields) . ')';

                $this->log->write($message);
                $this->output->writeln($message);

            }

        }

    }

    /**
     * Sets up the MySQL connection, runs the table generation and builds the query then runs it
     *
     * @throws \Exception
     */
    public function generate()
    {

        $this->dbAdapter->setDbName($this->schema->database->general->name);

        foreach($this->schema->database->tables as $table => $settings)
        {

            if($this->createDb())
            {

                $this->log->write('Created database ' . $this->schema->database->general->name);

            }
            else
            {

                throw new \Exception('Unable to create database');

            }

            if($this->dbAdapter->tableExists($table))
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

    /**
     * Runs through the directory and executes for all of the schema files in the schema directory
     *
     * @throws \Exception
     */
    public function run()
    {

        $dir = new \DirectoryIterator($this->schemaDir);

        foreach($dir as $fileInfo)
        {

            $this->setSchemaFile($fileInfo->getFilename());

            if(!$fileInfo->isDot() && $fileInfo->getFilename() != 'config')
            {

                if($this->exists())
                {

                    $this->generate();

                }
                else
                {

                    throw new \Exception('No schematics exist...');

                }

            }

        }

        $this->applyForeignKeys();

        $this->dbAdapter->commit();

    }

    /**
     * Runs the foreign keys update
     */
    public function applyForeignKeys()
    {

        if($this->foreignKeysSql != '')
        {

            if($this->dbAdapter->multiQuery($this->foreignKeysSql))
            {

                $this->output->writeln('<info>Applied foreign keys successfully</info>');

            }

        }
        else
        {

            $this->output->writeln('<info>No foreign keys to be applied</info>');

        }

    }

}