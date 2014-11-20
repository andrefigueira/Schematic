<?php
/**
 * The Schematic installer handles the initialization of Schematic for the project
 *
 * @author Andre Figueira <andre.figueira@me.com>
 */

namespace Library\Installer;

use Library\Migrations\Configurations;
use Library\Migrations\FileApi\FileGeneratorInferface;
use Symfony\Component\Console\Output\OutputInterface;

class SchematicInstaller
{

    /** @var string The file format type to use */
    protected $fileFormatType;

    public function __construct(OutputInterface $output, FileGeneratorInferface $fileGenerator)
    {

        $this->output = $output;
        $this->fileAdapter = $fileGenerator;

    }

    /**
     * Using the file adapter, generates format specific config contents for use in creating the config file
     *
     * @return mixed
     */
    private function defaultConfigFileData()
    {

        return $this->fileAdapter->convertToFormat(array(
            'driver' => Configurations::CONFIG_DEFAULT_DRIVER,
            'directory' =>  $_SERVER['PWD'] . '/' . Configurations::CONFIG_SCHEMA_FOLDER_NAME
        ));

    }

    /**
     * Using the file adapter creates default contents for the environment config file
     *
     * @return mixed
     */
    private function defaultEnvironmentConfigData()
    {

        return $this->fileAdapter->convertToFormat(array(
            'host' => '127.0.0.1',
            'user' => 'root',
            'pass' => ''
        ));

    }

    /**
     * Does some checks to see if the config file exists, if not creates the new file
     *
     * @return bool
     */
    private function createConfigFile()
    {

        $configFileName = Configurations::CONFIG_FILE_NAME . '.' . $this->fileFormatType;

        if(file_exists($configFileName))
        {

            $this->output->writeln('<fg=red>' . $configFileName . ' already exists!</fg=red>');

            return false;

        }
        else
        {


            if(@file_put_contents($configFileName, $this->defaultConfigFileData()))
            {

                $this->output->writeln('<info>Created ' . $configFileName . '</info>');

                return true;

            }
            else
            {

                $this->output->writeln('<fg=red>' . $configFileName . ' already exists!</fg=red>');

                return false;

            }

        }

    }

    /**
     * Creates the environment config file
     *
     * @param $dir
     * @return bool
     */
    private function createEnvironmentConfigFile($dir)
    {

        $configFileName = $dir . '/localhost.' . $this->fileFormatType;

        if(file_exists($configFileName))
        {

            $this->output->writeln('<fg=red>' . $configFileName . ' already exists!</fg=red>');

            return false;

        }
        else
        {

            if(@file_put_contents($configFileName, $this->defaultEnvironmentConfigData()))
            {

                $this->output->writeln('<info>Created ' . $configFileName . '</info>');

                return true;

            }
            else
            {

                $this->output->writeln('<fg=red>' . $configFileName . ' already exists!</fg=red>');

                return false;

            }

        }

    }

    /**
     * Creates the schema folder if it does not exist
     *
     * @return bool
     */
    private function createSchemaFolder()
    {

        if(is_dir(Configurations::CONFIG_SCHEMA_FOLDER_NAME))
        {

            $this->output->writeln('<fg=red>Schema folder already exists!</fg=red>');

            return false;

        }
        else
        {

            if(@mkdir(Configurations::CONFIG_SCHEMA_FOLDER_NAME))
            {

                $this->output->writeln('<info>Created ' . Configurations::CONFIG_SCHEMA_FOLDER_NAME . ' folder</info>');

                $this->createSchemaConfigFolder();

                return true;

            }
            else
            {

                $this->output->writeln('<fg=red>Unable to create schema folder, check permissions!</fg=red>');

                return false;

            }

        }

    }

    /**
     * Creates the schema config folder if it doesn't exist
     *
     * @return bool
     */
    private function createSchemaConfigFolder()
    {

        $dir = Configurations::CONFIG_SCHEMA_FOLDER_NAME . '/config';

        if(is_dir($dir))
        {

            $this->output->writeln('<fg=red>Schema config folder already exists!</fg=red>');

            return false;

        }
        else
        {

            if(@mkdir($dir))
            {

                $this->output->writeln('<info>Created ' . $dir . ' folder</info>');

                $this->createEnvironmentConfigFile($dir);

                return true;

            }
            else
            {

                $this->output->writeln('<fg=red>Unable to create schema config folder, check permissions!</fg=red>');

                return false;

            }

        }

    }

    /**
     * @param mixed $fileFormatType
     * @return $this
     */
    public function setFileFormatType($fileFormatType)
    {

        $this->fileFormatType = $fileFormatType;

        return $this;

    }

    /**
     * Runs the creation of the of the configurations for Schematic
     */
    public function run()
    {

        if($this->createConfigFile() && $this->createSchemaFolder())
        {

            $this->output->writeln('<info>Finished running init successfully</info>');

        }
        else
        {

            $this->output->writeln('<comment>Finished running init with errors</comment>');

        }

    }

}