<?php
/**
 * This class handles doing all the checks required for setting up a Schematic command
 *
 * @author Andre Figueira <andre.figueira@me.com>
 */

namespace Library\Helpers;

use Library\Migrations\Configurations;
use Library\Updater\SchematicUpdater;
use Symfony\Component\Console\Output\OutputInterface;

class SchematicHelper
{

    /**
     * This class handles the checks which are done for setting up a Schematic command
     *
     * @param OutputInterface $outputInterface
     * @param $params
     * @return array
     * @throws \Exception
     */
    public static function init(OutputInterface $outputInterface, $params)
    {

        if(!isset($params['fileType'])){ $params['fileType'] = false;}
        if(!isset($params['directory'])){ $params['directory'] = false;}

        $updater = new SchematicUpdater($outputInterface);

        if(!$updater->isCurrentVersionLatest())
        {

            $outputInterface->writeln('<comment>Your version of Schematic is out of date, please run schematic self-update to get the latest version...</comment>');

        }

        //Check where we are reading our configurations from, the options or the config file
        $migrationsConfigurations = new Configurations($outputInterface);
        $settingFileType = $migrationsConfigurations->fileType;

        if(!$settingFileType && !$params['fileType'])
        {

            throw new \Exception('There is no setting file e.g. .schematic.yaml defined, so pass in the file type or create the config file using -ft...');

        }

        if(!isset($migrationsConfigurations->config->directory) && !$params['directory'])
        {

            throw new \Exception('There is no directory setting in the ' . $migrationsConfigurations::CONFIG_FILE_NAME . ' config file, so path is through as an option using -d...');

        }

        if(!isset($migrationsConfigurations->config->driver))
        {

            throw new \Exception('You have not defined a valid database driver in your config');

        }

        //Set defaults for the options if the config file is set
        if($params['directory'])
        {

            $outputInterface->writeln('<comment>Using directory (' . $params['directory'] . ') passed in command!</comment>');

        }
        else
        {

            $directory = $migrationsConfigurations->config->directory;

        }

        if($params['fileType'])
        {

            $outputInterface->writeln('<comment>Using fileType (' . $params['fileType'] . ') passed in command!</comment>');

        }
        else
        {

            $fileType = $settingFileType;

        }

        $results = array(
            'fileType' => $fileType,
            'directory' => $directory,
            'driver' => $migrationsConfigurations->config->driver
        );

        return $results;

    }

    /**
     * Gets an instace of the database adapter
     *
     * @param $driver
     * @throws \Exception
     */
    public static function getDatabaseAdapter($driver)
    {

        if(in_array($driver, self::validDatabaseDrivers()))
        {

            $adapterClass = '\Library\Database\Adapters\\' . ucfirst($driver) . 'Adapter';

            $instance = new $adapterClass();

            return $instance;

        }
        else
        {

            throw new \Exception($driver . ' is not a valid database driver...');

        }

    }

    /**
     * Gets an instance of the file adapter
     *
     * @param $fileType
     * @throws \Exception
     */
    public static function getFileTypeGeneratorAdapter($fileType, $output)
    {

        if(in_array($fileType, self::validFileTypes()))
        {

            $adapterClass = '\Library\Migrations\FileApi\Adapters\\' . ucfirst($fileType) . 'Adapter';

            $instance = new $adapterClass($output);

            return $instance;

        }
        else
        {

            throw new \Exception($fileType . ' is not a valid database driver...');

        }

    }

    /**
     * Returns an array of valid database drivers
     *
     * @return array
     */
    public static function validDatabaseDrivers()
    {

        return array(
            'mysql'
        );

    }

    /**
     * Returns an array of valid file types
     *
     * @return array
     */
    public static function validFileTypes()
    {

        return array(
            'yaml',
            'json'
        );

    }

}