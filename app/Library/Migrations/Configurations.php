<?php

namespace Library\Migrations;

use Library\Cli\OutputInterface;

class Configurations
{

    public $fileType;

    public $configFileExists = false;

    public $configFilePath;

    public $config;

    const CONFIG_FILE_NAME = '.schematic';

    public function __construct(OutputInterface $output)
    {

        $this->output = $output;

        $this->fileType = $this->getConfigFileType();

        $this->config = $this->fetchConfigurations();

    }

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

    private function allowedConfigurationFileTypes()
    {

        return array(
            'json',
            'yaml'
        );

    }

}