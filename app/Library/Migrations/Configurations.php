<?php
/**
 * This class handles the configurations for the Schematic setup, checks the file types and loads config data
 */

namespace Library\Migrations;

use Library\Cli\OutputInterface;

class Configurations
{

    /** @var bool The file type being used */
    public $fileType;

    /** @var bool The variable which determines if a config file exists */
    public $configFileExists = false;

    /** @var string The file path for the config file */
    public $configFilePath;

    /** @var object A stdObj which is filled with data from a reflection call that loads config data from the config file */
    public $config;

    /** @var string The prefix of the default schematic configuration file */
    const CONFIG_FILE_NAME = '.schematic';

    /**
     * The construct expects an output interface so it can print to screen it also runs the getConfigFileType to determine
     * what the file type is on it's own, and after checks if it can fetch configuration information from the config file
     * if it exists
     *
     * @param OutputInterface $output
     * @throws \Exception
     */
    public function __construct(OutputInterface $output)
    {

        $this->output = $output;

        $this->fileType = $this->getConfigFileType();

        $this->config = $this->fetchConfigurations();

    }

    /**
     * Loops through the allowed config file types and checks if any schematic config file exists for the project and it
     * returns the file type it's found if there is one
     *
     * @return bool
     */
    private function getConfigFileType()
    {

        foreach($this->allowedConfigurationFileTypes() as $allowedFileType)
        {

            $fileToCheckFor = self::CONFIG_FILE_NAME . '.' . $allowedFileType;

            if(file_exists($fileToCheckFor))
            {

                $this->output->writeln('<info>Schematic config files are in ' . $allowedFileType . ' format</info>');

                $this->configFileExists = true;
                $this->configFilePath = $fileToCheckFor;

                return $allowedFileType;

            }

        }

        return false;

    }

    /**
     * Uses the RelectionMethod class to try and call a fileApiAdapter class to convert the formatted contents of a configuration
     * file into a stdObject which we can use
     *
     * @throws \Exception
     */
    private function fetchConfigurations()
    {

        if($this->configFileExists)
        {

            $contents = @file_get_contents($this->configFilePath);

            if($contents)
            {

                $adapterClass = '\Library\Migrations\FileApi\Adapters\\' . ucfirst($this->fileType) . 'Adapter';

                $reflectionMethod = new \ReflectionMethod($adapterClass, 'convertToObject');

                return $reflectionMethod->invokeArgs(new $adapterClass($this->output), array($contents));

            }
            else
            {

                throw new \Exception('Failed to fetch contents of config file, reset it as it\'s corrupt');

            }

        }

    }

    /**
     * Returns an array of the allowed configuration file types
     *
     * @return array
     */
    private function allowedConfigurationFileTypes()
    {

        return array(
            'json',
            'yaml'
        );

    }

}