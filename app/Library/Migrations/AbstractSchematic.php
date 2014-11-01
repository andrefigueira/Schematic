<?php

namespace Library\Migrations;

use Library\Database\DatabaseInterface;
use Library\Logger\LogInterface;
use Library\Cli\OutputInterface;
use Library\Migrations\FileApi\FileGeneratorInferface;

abstract class AbstractSchematic
{

    /** @var string the format type to use */
    protected $formatType;

    /** @var string The default schema directory */
    protected $directory = '';

    /** @var string The environment that the Schematic is running on */
    protected $environment;

    /** @var object Object of properties pertainent to the database connected to */
    protected $environmentConfigs;

    /** @var string The database to use for the import */
    protected $database;

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

    /**
     * Setter for the format type
     *
     * @param $formatType
     * @return $this
     */
    public function setFileFormatType($formatType)
    {

        $this->formatType = $formatType;

        return $this;

    }

    /**
     * Setter for the working directory
     *
     * @param $directory
     * @return $this
     */
    public function setDirectory($directory)
    {

        $this->directory = $directory;

        return $this;

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
        $environmentFile = $environmentPath . $this->environment . '.' . $this->formatType;

        if($environmentFile)
        {

            $this->environmentConfigs = @file_get_contents($environmentFile);

            if($this->environmentConfigs)
            {

                $this->environmentConfigs = $this->fileGenerator->convertToObject($this->environmentConfigs);

                $this->setDbAdapterConfigs();

            }
            else
            {

                throw new \Exception('Unable to read environment config file, ensure you have created: ' . $environmentFile);

            }

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
            ->connect();

    }

}