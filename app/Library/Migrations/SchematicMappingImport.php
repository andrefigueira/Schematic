<?php

namespace Library\Migrations;

class SchematicMappingImport extends AbstractSchematic
{

    /**
     * Runs the application, sets the environment configs based on the environment and runs the mapper and generator.
     */
    public function run()
    {

        $this->setEnvironmentConfigs($this->environment);

        $this->dbAdapter->setDbName($this->database);

        $this->fileGenerator
            ->setDirectory($this->directory)
            ->setDbName($this->database)
            ->setDatabaseVariables($this->dbAdapter->fetchDatabaseVariables())
            ->mapAndGenerateSchema($this->dbAdapter->mapDatabase());

        $this->output->writeln('<info>Mapping of (</info>' . $this->database . '<info>) is complete</info>');

    }

}