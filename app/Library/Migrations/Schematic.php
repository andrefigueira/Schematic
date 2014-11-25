<?php
/**
 * Schematic is a MySQL database creation and maintenance script, It allows you to define a schema in a specific format
 * and run a simple script to do the creation or updates to your database, If you change your schema file and run the
 * script it will then run through and make the updates to the database.
 *
 * @author <Andre Figueira> andre.figueira@me.com
 *
 */

namespace Library\Migrations;

class Schematic extends AbstractSchematic
{

    /** @var string The base directory for the schematic install */
    protected $baseDir = '';

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

    protected $foreignKeysSql;

    protected $indexesArray;

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
     * Checks if the schema file exists, if it does, assigns the interpretted schema file to a schema property within the instance
     *
     * @throws \Exception
     */
    private function exists()
    {

        $this->realSchemaDir = $this->directory;

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

                        $this->schema = $this->fileGenerator->convertToObject($this->schema);

                        $this->dbAdapter->setSchema($this->schema);

                    }
                    else
                    {

                        throw new \Exception('Unable to load the schema file: ' . $specificSchemaConfFile);

                    }

                }
                else
                {

                    throw new \Exception('Schema file does not exist: ' . $specificSchemaConfFile);

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

            if($this->dbAdapter->migrateTable($table, $settings))
            {

                $this->output->writeln('<info>Successfully migrated the ' . $table . ' table</info>');

            }
            else
            {

                $this->output->writeln('<error>Failed to migrate the ' . $table . ' table</error>');

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
    private function isEmptyDir($dir)
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

        $this->output->writeln('<info>Begining migrations...</info>');

        $dir = new \DirectoryIterator($this->directory);

        foreach($dir as $fileInfo)
        {

            $fileName = $fileInfo->getFilename();

            $this->setSchemaFile($fileName);

            if(!$fileInfo->isDot() && $fileName != 'config' && $fileName != '.DS_Store')
            {

                $filePath = $fileInfo->getPath() . '/' . $fileName;

                if($fileInfo->isDir())
                {

                    $subDir = new \DirectoryIterator($filePath);

                    foreach($subDir as $subFileInfo)
                    {

                        $this->setSchemaFile($fileName . '/' . $subFileInfo->getFilename());

                        if(!$subFileInfo->isDot())
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

                }
                else
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

        }

        $this->dbAdapter->applyForeignKeys();

        $this->output->writeln('<info>Migrations completed</info>');

    }

}