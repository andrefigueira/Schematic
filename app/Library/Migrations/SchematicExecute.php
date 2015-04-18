<?php

namespace Library\Migrations;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Library\Helpers\SchematicHelper;
use Library\Database\Adapters\Mysql\Adapter;
use Library\Database\Adapters\Mysql\Database;

/**
 * Class SchematicExecute
 * @package Library\Migrations
 */
class SchematicExecute extends AbstractSchematic
{
    /**
     * @var string
     */
    protected $schema;

    /**
     * @return bool
     */
    protected function validateSchema()
    {
        return true;
    }

    /**
     * @throws \Exception
     * @throws \Library\Database\Adapters\Mysql\Exception
     */
    protected function migrate()
    {
        if ($this->validateSchema() === false) {
            throw new \Exception('Invalid schema syntax, please check your schema file!');
        }

        SchematicHelper::writeln('<info>Setting up connections...</info>');

        $adapter = new Adapter($this->schema->database->general->name);
        $adapter
            ->setHost($this->environmentConfigs->host)
            ->setUser($this->environmentConfigs->user)
            ->setPass($this->environmentConfigs->pass)
            ->connect()
        ;

        $database = new Database($adapter);
        $database
            ->setName($this->schema->database->general->name)
            ->setCharset($this->schema->database->general->charset)
            ->setCollation($this->schema->database->general->collation)
            ->setEngine($this->schema->database->general->engine)
        ;

        try {
            if ($database->exists()) {
                SchematicHelper::writeln('<info>Database exists...</info>');

                if ($database->modified()) {
                    SchematicHelper::writeln('<comment>Database modified...</comment>');

                    $database->update();
                } else {
                    SchematicHelper::writeln('<info>Database unchanged</info>');
                }
            } else {
                SchematicHelper::writeln('<comment>Database does not exist, creating it now...</comment>');

                $database->create();
            }

            $adapter->useDatabase($database->getName());

            SchematicHelper::writeln('<bg=green;fg=black;>Database checks completed successfully!</bg=green;fg=black;>');
            SchematicHelper::writeln('<info>Starting table checks</info>');

            foreach ($this->schema->database->tables as $table => $fields) {
                SchematicHelper::writeln('<info>Checking table: </info>'.$table);

                $table = $database->getTable($table);

                if ($table->exists()) {
                    SchematicHelper::writeln('<info>Table exists, proceeding with field checks</info>');
                } else {
                    SchematicHelper::writeln('<comment>Table does not exist, creating it now</comment>');

                    $table->create();
                }

                SchematicHelper::writeln('<bg=green;fg=black;>Table checks completed successfully!</bg=green;fg=black;>');
                SchematicHelper::writeln('<info>Starting field checks</info>');

                foreach ($fields->fields as $name => $properties) {
                    SchematicHelper::writeln('<info>Checking field: </info>'.$name);

                    $field = $table->getField($name, $properties);

                    if ($field->exists()) {
                        SchematicHelper::writeln('<info>Field exists</info>');

                        if ($field->modified()) {
                            SchematicHelper::writeln('<comment>Field modified, proceeding to update</comment>');

                            $field->update();
                        } else {
                            SchematicHelper::writeln('<info>Field unchanged</info>');
                        }
                    } else {
                        SchematicHelper::writeln('<comment>Field does not exist, creating it now</comment>');

                        $field->create();
                    }

                    SchematicHelper::writeln('<info>Finished processing field: </info>'.$name.'');
                }

                SchematicHelper::writeln('<bg=green;fg=black;>Completed table migrations successfully!</bg=green;fg=black;>');
            }

            SchematicHelper::writeln('<info>-----------------------------</info>');
            SchematicHelper::writeln('<bg=green;fg=black;>Full database has been migrated</bg=green;fg=black;>');
        } catch (Exception $e) {
            SchematicHelper::writeln($e->getMessage());
        }
    }

    public function importConstraints()
    {
        $adapter = new Adapter($this->schema->database->general->name);
        $adapter
            ->setHost($this->environmentConfigs->host)
            ->setUser($this->environmentConfigs->user)
            ->setPass($this->environmentConfigs->pass)
            ->connect()
        ;

        $database = new Database($adapter);
        $database
            ->setName($this->schema->database->general->name)
            ->setCharset($this->schema->database->general->charset)
            ->setCollation($this->schema->database->general->collation)
            ->setEngine($this->schema->database->general->engine)
        ;

        $adapter->useDatabase($database->getName());

        foreach ($this->schema->database->tables as $table => $fields) {
            SchematicHelper::writeln('<fg=yellow;>- Checking field relationships for table ' . $table . '</fg=yellow;>');

            $table = $database->getTable($table);
            foreach ($fields->fields as $name => $properties) {
                SchematicHelper::writeln('<fg=yellow;>-- Checking field relationships for ' . $name . '</fg=yellow;>');
                $field = $table->getField($name, $properties);
                if ($field->relationSettingExists()) {
                    SchematicHelper::writeln('<fg=yellow>---- Foreign key setting exists in Schema</fg=yellow;>');
                    if ($field->relationExists() === true) {
                        SchematicHelper::writeln('<fg=green;>---- Relationship exists, no pending action</fg=green;>');
                    } else {
                        if ($field->createRelation()) {
                            SchematicHelper::writeln('<fg=green;>---- Created relationship successfully</fg=green;>');
                        } else {
                            SchematicHelper::writeln('<error>---- Failed to create relationship, please process manually and remap</error>');
                        }
                    }
                } else {
                    SchematicHelper::writeln('<fg=yellow;>---- No relation setting exists for ' . $name . '</fg=yellow;>');
                }
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function run()
    {
        SchematicHelper::writeln('<info>Beginning migrations...</info>');

        $filesystem = new Filesystem(new Local($this->directory));

        if (!is_dir($this->directory)) {
            throw new \Exception('Schema folder does not exist...');
        }

        SchematicHelper::writeln('<info>Scanning schema folder</info>');

        $directoryContents = $filesystem->listContents();

        if (count($directoryContents) > 0) {
            foreach ($directoryContents as $file) {
                if ($file['type'] == 'dir') {
                    $directoryContents = $filesystem->listContents($file['path']);

                    if (count($directoryContents) > 0) {
                        foreach ($directoryContents as $file) {
                            if (strstr($file['basename'], $this->formatType)) {
                                SchematicHelper::writeln('<info>Loading schema file: </info>'.$file['path']);

                                $this->schema = $filesystem->read($file['path']);

                                if ($this->schema) {
                                    $this->schema = $this->fileGenerator->convertToObject($this->schema);

                                    $this->migrate();
                                } else {
                                    throw new \Exception('Unable to load schema file');
                                }
                            }
                        }
                    } else {
                        SchematicHelper::writeln('<error>No schema files exist...</error>');
                    }
                } else {
                    SchematicHelper::writeln('<comment>Found free floating schema file ('.$file['path'].'), please place this in a subfolder beneath the schemas dir, skipping...</comment>');
                }
            }

            $filesystem = new Filesystem(new Local($this->directory));

            if (!is_dir($this->directory)) {
                throw new \Exception('Schema folder does not exist...');
            }

            SchematicHelper::writeln('<info>Scanning schema folder</info>');

            $directoryContents = $filesystem->listContents();

            foreach ($directoryContents as $file) {
                if ($file['type'] == 'dir') {
                    $directoryContents = $filesystem->listContents($file['path']);

                    if (count($directoryContents) > 0) {
                        foreach ($directoryContents as $file) {
                            if (strstr($file['basename'], $this->formatType)) {
                                SchematicHelper::writeln('<info>Loading schema file: </info>'.$file['path']);

                                $this->schema = $filesystem->read($file['path']);

                                if ($this->schema) {
                                    $this->schema = $this->fileGenerator->convertToObject($this->schema);

                                    $this->importConstraints();
                                } else {
                                    throw new \Exception('Unable to load schema file');
                                }
                            }
                        }
                    } else {
                        SchematicHelper::writeln('<error>No schema files exist...</error>');
                    }
                } else {
                    SchematicHelper::writeln('<comment>Found free floating schema file ('.$file['path'].'), please place this in a subfolder beneath the schemas dir, skipping...</comment>');
                }
            }
        } else {
            SchematicHelper::writeln('<comment>No schema files found, be sure to place them in a subfolder beneath the "schemas" directory</comment>');
        }
    }
}
