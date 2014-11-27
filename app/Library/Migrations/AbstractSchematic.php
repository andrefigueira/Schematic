<?php

namespace Library\Migrations;

use Library\Logger\LogInterface;
use Library\Migrations\FileApi\FileGeneratorInferface;
use Symfony\Component\Console\Output\OutputInterface;

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

    /** @var object Object of configurations */
    protected $config;

    /**
     * We're injecting a logger and a database adapter into the Schematic which are interchangeable
     *
     * @param LogInterface $log
     * @param FileGeneratorInferface $fileGenerator
     */
    public function __construct(LogInterface $log, FileGeneratorInferface $fileGenerator)
    {

        $this->log = $log;
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

        if(substr($directory, -1) != '/'){ $directory = $directory . '/';}

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

    public function setEnvironmentConfigs($environmentConfigs)
    {

        $this->environmentConfigs = $environmentConfigs;

        return $this;

    }

}