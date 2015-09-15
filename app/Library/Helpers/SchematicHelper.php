<?php

namespace Library\Helpers;

use Library\Migrations\Configurations;
use Library\Updater\SchematicUpdater;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SchematicHelper
 *
 * This class handles doing all the checks required for setting up a Schematic command.
 *
 * @package Library\Helpers
 */
class SchematicHelper
{
    /**
     * This class handles the checks which are done for setting up a Schematic command.
     *
     * @param $params
     * @param Di $di
     *
     * @return array
     *
     * @throws \Exception
     */
    public static function init($params, $di)
    {
		$outputInterface = $di->get('output');

        if (!isset($params['fileType'])) {
            $params['fileType'] = false;
        }
        if (!isset($params['directory'])) {
            $params['directory'] = false;
        }
        if (!isset($params['environment'])) {
            $params['environment'] = false;
        }

        $updater = new SchematicUpdater($outputInterface);

        if (!$updater->isCurrentVersionLatest()) {
            $outputInterface->writeln('<comment>Your version of Schematic is out of date, please run schematic self-update to get the latest version...</comment>');
        }

        //Check where we are reading our configurations from, the options or the config file
        $migrationsConfigurations = new Configurations($outputInterface);
        $settingFileType = $migrationsConfigurations->fileType;

        if (!$settingFileType && !$params['fileType']) {
            throw new \Exception('There is no setting file e.g. .schematic.yaml defined, so pass in the file type or create the config file using -ft...');
        }

        if (!isset($migrationsConfigurations->config->directory) && !$params['directory']) {
            throw new \Exception('There is no directory setting in the '.$migrationsConfigurations::CONFIG_FILE_NAME.' config file, so path is through as an option using -d...');
        }

        if (!isset($migrationsConfigurations->config->driver)) {
            throw new \Exception('You have not defined a valid database driver in your config');
        }

        //Set defaults for the options if the config file is set
        if ($params['directory']) {
            $outputInterface->writeln('<comment>Using directory ('.$params['directory'].') passed in command!</comment>');
        } else {
            $directory = $migrationsConfigurations->config->directory;
        }

        if (isset($migrationsConfigurations->config->environments->{$params['environment']})) {
            $environmentConfigs = $migrationsConfigurations->config->environments->{$params['environment']};
        } else {
            $environmentConfigs = null;

            if ($params['environment'] != null) {
                throw new \Exception('Environment '.$params['environment'].' does not exist...');
            }
        }

        if ($params['fileType']) {
            $fileType = $params['fileType'];

            $outputInterface->writeln('<comment>Using fileType ('.$params['fileType'].') passed in command!</comment>');
        } else {
            $fileType = $settingFileType;
        }

        $results = array(
            'fileType' => $fileType,
            'directory' => $directory,
            'driver' => $migrationsConfigurations->config->driver,
            'environmentConfigs' => $environmentConfigs,
        );

        return $results;
    }

    public static function underscoreToCamelCase($string)
    {
        $func = create_function('$c', 'return strtoupper($c[1]);');

        return preg_replace_callback('/_([a-z])/', $func, $string);
    }

    public static function writeln($messages, $type = ConsoleOutput::OUTPUT_NORMAL)
    {
        $output = new ConsoleOutput();
        $output->writeln($messages, $type);
    }
}
