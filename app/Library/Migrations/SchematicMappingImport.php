<?php

namespace Library\Migrations;

use Library\Database\Adapters\Mysql\Adapter;
use Library\Helpers\SchematicHelper;

/**
 * Class SchematicMappingImport
 * @package Library\Migrations
 */
class SchematicMappingImport extends AbstractSchematic
{

    /**
     * Runs the application, sets the environment configs based on the environment and runs the mapper and generator.
     *
     * @throws \Exception
     * @throws \Library\Database\Adapters\Mysql\Exception
     */
    public function run()
    {
        $adapter = new Adapter($this->database);
        $adapter
            ->setHost($this->environmentConfigs->host)
            ->setUser($this->environmentConfigs->user)
            ->setPass($this->environmentConfigs->pass)
            ->connect()
            ->useDatabase($adapter->getDatabaseName())
        ;

        $this->fileGenerator
            ->setDirectory($this->directory)
            ->setDbName($this->database)
            ->setDatabaseVariables($adapter->fetchDatabaseVariables())
            ->mapAndGenerateSchema($adapter->mapDatabase())
        ;

        SchematicHelper::writeln('<info>Mapping of (</info>' . $this->database . '<info>) is complete</info>');
    }
}
