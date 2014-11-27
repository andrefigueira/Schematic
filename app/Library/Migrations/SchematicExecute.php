<?php

namespace Library\Migrations;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Library\Helpers\SchematicHelper;
use \Library\Database\Adapters\Mysql\Adapter;
use \Library\Database\Adapters\Mysql\Database;
use Symfony\Component\Console\Output\OutputInterface;

class SchematicExecute extends AbstractSchematic
{

    protected $schema;

    protected function validateSchema()
    {

        return true;

    }

    protected function migrate()
    {

        if($this->validateSchema() === false){ throw new \Exception('Invalid schema syntax, please check your schema file!');}

        SchematicHelper::writeln('<info>Setting up connections...</info>');

        $adapter = new Adapter($this->schema->database->general->name);
        $adapter
            ->setHost($this->environmentConfigs->host)
            ->setUser($this->environmentConfigs->user)
            ->setPass($this->environmentConfigs->pass);

        $database = new Database($adapter);
        $database
            ->setName($this->schema->database->general->name)
            ->setCharset($this->schema->database->general->charset)
            ->setCollation($this->schema->database->general->collation)
            ->setEngine($this->schema->database->general->engine);

        try
        {

            if($database->exists())
            {

                SchematicHelper::writeln('<info>Database exists...</info>');

                if($database->modified())
                {

                    SchematicHelper::writeln('<comment>Database modified...</comment>');

                    $database->update();

                }
                else
                {

                    SchematicHelper::writeln('<info>Database unchanged</info>');

                }

            }
            else
            {

                SchematicHelper::writeln('<comment>Database does not exist, creating is now...</comment>');

                $database->create();

            }

            SchematicHelper::writeln('<bg=green;fg=black;>Database checks completed successfully!</bg=green;fg=black;>');
            SchematicHelper::writeln('<info>Starting table checks</info>');

            foreach($this->schema->database->tables as $table => $fields)
            {

                SchematicHelper::writeln('<info>Checking table: </info>' . $table);

                $table = $database->getTable($table);

                if($table->exists())
                {

                    SchematicHelper::writeln('<info>Table exists, proceeding with field checks</info>');

                }
                else
                {

                    SchematicHelper::writeln('<comment>Table does not exist, creating it now</comment>');

                    $table->create();

                }

                SchematicHelper::writeln('<bg=green;fg=black;>Table checks completed successfully!</bg=green;fg=black;>');
                SchematicHelper::writeln('<info>Starting field checks</info>');

                foreach($fields->fields as $name => $properties)
                {

                    SchematicHelper::writeln('<info>Checking field: </info>' . $name);

                    $field = $table->getField($name, $properties);

                    if($field->exists())
                    {

                        SchematicHelper::writeln('<info>Field exists</info>');

                        if($field->modified())
                        {

                            SchematicHelper::writeln('<comment>Field modified, proceeding to update</comment>');

                            $field->update();

                        }
                        else
                        {

                            SchematicHelper::writeln('<info>Field unchanged</info>');

                        }

                    }
                    else
                    {

                        SchematicHelper::writeln('<comment>Field does not exist, creating it now</comment>');

                        $field->create();

                    }

                    SchematicHelper::writeln('<info>Finished processing field: </info>' . $name . '');

                }

                SchematicHelper::writeln('<bg=green;fg=black;>Completed migrations successfully!</bg=green;fg=black;>');

            }

        }
        catch(Exception $e)
        {

            SchematicHelper::writeln($e->getMessage());

        }

    }

    public function run()
    {

        SchematicHelper::writeln('<info>Beginning migrations...</info>');

        $filesystem = new Filesystem(new Local($this->directory));

        if(!is_dir($this->directory)){ throw new \Exception('Schema folder does not exist...');}

        SchematicHelper::writeln('<info>Scanning schema folder</info>');

        $directoryContents = $filesystem->listContents();

        if(count($directoryContents) > 0)
        {

            foreach($directoryContents as $file)
            {

                if($file['type'] == 'dir')
                {

                    $directoryContents = $filesystem->listContents($file['path']);

                    if(count($directoryContents) > 0)
                    {

                        foreach($directoryContents as $file)
                        {

                            if(strstr($file['basename'], $this->formatType))
                            {

                                SchematicHelper::writeln('<info>Loading schema file: </info>' . $file['path']);

                                $this->schema = $filesystem->read($file['path']);

                                if($this->schema)
                                {

                                    $this->schema = $this->fileGenerator->convertToObject($this->schema);

                                    $this->migrate();

                                }
                                else
                                {

                                    throw new \Exception('Unable to load schema file');

                                }

                            }

                        }

                    }
                    else
                    {

                        SchematicHelper::writeln('<error>No schema files exist...</error>');

                    }

                }
                else
                {

                    SchematicHelper::writeln('<comment>Found free floating schema file (' . $file['path'] . '), please place this in a subfolder beneath the schemas dir, skipping...</comment>');

                }

            }

        }
        else
        {

            SchematicHelper::writeln('<comment>No schema files found, be sure to place them in a subfolder beneath the "schemas" directory</comment>');

        }

    }

}