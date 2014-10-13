<?php

namespace Controllers\Migrations;

use Controllers\Database\DatabaseInterface;
use Controllers\Logger\LogInterface;
use Controllers\Cli\OutputInterface;
use Controllers\Migrations\Generators\FileGeneratorInferface;

class SchematicMappingImport
{

    protected $directory;

    protected $database;

    protected $environment;

    protected $environmentConfigs;

    /**
     * We're injecting a logger and a database adapter into the Schematic which are interchangeable
     *
     * @param LogInterface $log
     * @param DatabaseInterface $dbAdapter
     * @param OutputInterface $output
     * @param FileGeneratorInferface $fileGenerator
     */
    public function __construct(LogInterface $log, DatabaseInterface $dbAdapter, OutputInterface $output, FileGeneratorInferface $fileGenerator)
    {

        $this->log = $log;
        $this->dbAdapter = $dbAdapter;
        $this->output = $output;
        $this->fileGenerator = $fileGenerator;

        return $this;

    }

    public function setDir($directory)
    {

        $this->directory = $directory;

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

        $environmentPath = $this->directory . 'config/';
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
            ->setDbName($this->database)
            ->connect();

    }

    /**
     * @param mixed $database
     * @return $this
     */
    public function setDatabase($database)
    {

        $this->database = $database;

        return $this;

    }

    public function run()
    {

        $this->setEnvironmentConfigs($this->environment);

        $this->fileGenerator->mapAndGenerateSchema($this->dbAdapter->mapDatabase());

    }

}